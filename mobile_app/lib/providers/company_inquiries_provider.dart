import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/inquiry_model.dart';

/// State for the Centralized Inquiries & Query Pinning Hub
class CompanyInquiriesState {
  final bool isLoading;
  final bool isSyncing;
  final List<InquiryModel> inquiries;
  final int? selectedJobId;
  final String selectedPlatform; // 'all', 'linkedin', 'naukri', 'indeed', 'direct'
  final String selectedStatus; // 'all', 'new', 'reviewed', 'shortlisted', 'rejected'
  final String searchQuery;
  final String? errorMessage;
  final String? successMessage;

  const CompanyInquiriesState({
    this.isLoading = false,
    this.isSyncing = false,
    this.inquiries = const [],
    this.selectedJobId,
    this.selectedPlatform = 'all',
    this.selectedStatus = 'all',
    this.searchQuery = '',
    this.errorMessage,
    this.successMessage,
  });

  /// Filtered inquiries with pinned items always placed at the very top!
  List<InquiryModel> get sortedInquiries {
    final list = [...inquiries];
    list.sort((a, b) {
      if (a.isPinned && !b.isPinned) return -1;
      if (!a.isPinned && b.isPinned) return 1;
      return b.createdAt.compareTo(a.createdAt);
    });
    return list;
  }

  CompanyInquiriesState copyWith({
    bool? isLoading,
    bool? isSyncing,
    List<InquiryModel>? inquiries,
    int? selectedJobId,
    bool clearSelectedJob = false,
    String? selectedPlatform,
    String? selectedStatus,
    String? searchQuery,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return CompanyInquiriesState(
      isLoading: isLoading ?? this.isLoading,
      isSyncing: isSyncing ?? this.isSyncing,
      inquiries: inquiries ?? this.inquiries,
      selectedJobId: clearSelectedJob ? null : (selectedJobId ?? this.selectedJobId),
      selectedPlatform: selectedPlatform ?? this.selectedPlatform,
      selectedStatus: selectedStatus ?? this.selectedStatus,
      searchQuery: searchQuery ?? this.searchQuery,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class CompanyInquiriesNotifier extends StateNotifier<CompanyInquiriesState> {
  CompanyInquiriesNotifier() : super(const CompanyInquiriesState());

  final _api = ApiClient();

  /// Fetch inquiries with role-wise job filtering and query parameters
  Future<void> fetchInquiries({
    int? jobId,
    String? platform,
    String? status,
    String? search,
  }) async {
    // Only block with full-screen spinner if we have NO items yet (Stale-While-Revalidate)
    if (state.inquiries.isEmpty) {
      state = state.copyWith(isLoading: true, clearMessages: true);
    }
    try {
      final queryParams = <String, dynamic>{};
      final targetJobId = jobId ?? state.selectedJobId;
      if (targetJobId != null && targetJobId > 0) {
        queryParams['job_id'] = targetJobId;
      }
      final targetPlatform = platform ?? state.selectedPlatform;
      if (targetPlatform != 'all') {
        queryParams['platform'] = targetPlatform;
      }
      final targetStatus = status ?? state.selectedStatus;
      if (targetStatus != 'all') {
        queryParams['status'] = targetStatus;
      }
      final targetSearch = search ?? state.searchQuery;
      if (targetSearch.isNotEmpty) {
        queryParams['search'] = targetSearch;
      }

      final response = await _api.dio.get(
        ApiEndpoints.companyInquiries,
        queryParameters: queryParams,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['inquiries'] ?? response.data['data']?['inquiries'] ?? []) as List;
        final items = list.map((e) => InquiryModel.fromJson(Map<String, dynamic>.from(e))).toList();
        state = state.copyWith(
          inquiries: items,
          isLoading: false,
          selectedJobId: targetJobId,
          selectedPlatform: targetPlatform,
          selectedStatus: targetStatus,
          searchQuery: targetSearch,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load inquiries.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// Toggle Query Pinning: Pins an inquiry to top or unpins it with instant optimistic UI (0ms)
  Future<bool> togglePin(int inquiryId) async {
    // 1. Capture previous state for instant rollback on network error
    final previousInquiries = state.inquiries;
    final index = previousInquiries.indexWhere((inq) => inq.id == inquiryId);
    if (index == -1) return false;

    final target = previousInquiries[index];
    final newPinnedStatus = !target.isPinned;

    // 2. Instant Optimistic UI Update (Zero latency UI feedback!)
    final optimisticList = [...previousInquiries];
    optimisticList[index] = target.copyWith(isPinned: newPinnedStatus);
    state = state.copyWith(
      inquiries: optimisticList,
      successMessage: newPinnedStatus ? 'Query pinned to top!' : 'Query unpinned.',
    );

    // 3. Background asynchronous network sync
    try {
      final response = await _api.dio.post(ApiEndpoints.togglePin(inquiryId));
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (!isSuccess) {
        // Revert on failure
        state = state.copyWith(inquiries: previousInquiries, errorMessage: 'Could not sync pin status.');
        return false;
      }
      return true;
    } catch (e) {
      // Revert on error
      state = state.copyWith(
        inquiries: previousInquiries,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Reply to a candidate query or application
  Future<bool> replyToInquiry(int inquiryId, String reply) async {
    try {
      final response = await _api.dio.post(
        ApiEndpoints.replyInquiry(inquiryId),
        data: {'reply': reply},
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final rawInquiry = response.data['inquiry'] ?? response.data['data']?['inquiry'];
        if (rawInquiry != null) {
          final updatedInq = InquiryModel.fromJson(Map<String, dynamic>.from(rawInquiry));
          final updated = state.inquiries.map((inq) {
            return inq.id == inquiryId ? updatedInq : inq;
          }).toList();

          state = state.copyWith(
            inquiries: updated,
            successMessage: 'Reply sent to candidate!',
          );
          return true;
        }
      }
      return false;
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
      return false;
    }
  }

  /// Send WhatsApp reply to candidate via Twilio API
  Future<bool> replyWhatsApp(int inquiryId, String message, {String? candidatePhone}) async {
    try {
      final payload = <String, dynamic>{
        'company_reply': message,
      };
      if (candidatePhone != null && candidatePhone.isNotEmpty) {
        payload['candidate_phone'] = candidatePhone;
      }

      final response = await _api.dio.post(
        ApiEndpoints.inquiryWhatsAppReply(inquiryId),
        data: payload,
      );

      final isSuccess = response.data['success'] == true || response.statusCode == 200;

      if (isSuccess) {
        final rawInquiry = response.data['inquiry'] ?? response.data['data']?['inquiry'];
        if (rawInquiry != null) {
          final updatedInq = InquiryModel.fromJson(Map<String, dynamic>.from(rawInquiry));
          final updated = state.inquiries.map((inq) {
            return inq.id == inquiryId ? updatedInq : inq;
          }).toList();

          state = state.copyWith(
            inquiries: updated,
            successMessage: 'WhatsApp message sent to candidate!',
          );
          return true;
        }
      }
      state = state.copyWith(errorMessage: response.data['error'] ?? 'WhatsApp dispatch failed.');
      return false;
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
      return false;
    }
  }

  /// Update candidate status (shortlisted, reviewed, rejected) with optimistic UI & optional interview schedule
  Future<bool> updateStatus(
    int inquiryId,
    String status, {
    DateTime? scheduledAt,
    String? meetingType,
    String? meetingLink,
    String? notes,
    bool sendEmail = true,
  }) async {
    final previousInquiries = state.inquiries;
    final index = previousInquiries.indexWhere((inq) => inq.id == inquiryId);
    if (index == -1) return false;

    // Instant optimistic update
    final optimisticList = [...previousInquiries];
    optimisticList[index] = previousInquiries[index].copyWith(
      status: status,
      interviewScheduledAt: scheduledAt?.toIso8601String(),
      meetingType: meetingType,
      meetingLinkOrLocation: meetingLink,
      interviewNotes: notes,
    );
    state = state.copyWith(
      inquiries: optimisticList,
      successMessage: status == 'shortlisted' && sendEmail
          ? 'Candidate shortlisted! Interview invitation sent.'
          : 'Candidate marked as $status.',
    );

    final payload = <String, dynamic>{
      'status': status,
    };
    if (status == 'shortlisted') {
      if (scheduledAt != null) {
        payload['interview_scheduled_at'] = scheduledAt.toIso8601String();
      }
      if (meetingType != null) {
        payload['meeting_type'] = meetingType;
      }
      if (meetingLink != null && meetingLink.isNotEmpty) {
        payload['meeting_link_or_location'] = meetingLink;
      }
      if (notes != null && notes.isNotEmpty) {
        payload['interview_notes'] = notes;
      }
      payload['send_email'] = sendEmail;
    }

    try {
      final response = await _api.dio.patch(
        ApiEndpoints.updateInquiryStatus(inquiryId),
        data: payload,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (!isSuccess) {
        state = state.copyWith(inquiries: previousInquiries, errorMessage: 'Failed to update status.');
        return false;
      }

      // Sync updated data from backend response if present
      final rawInquiry = response.data['inquiry'] ?? response.data['data']?['inquiry'];
      if (rawInquiry != null) {
        final serverInquiry = InquiryModel.fromJson(Map<String, dynamic>.from(rawInquiry));
        final updated = state.inquiries.map((inq) {
          return inq.id == inquiryId ? serverInquiry : inq;
        }).toList();
        state = state.copyWith(inquiries: updated);
      }

      return true;
    } catch (e) {
      state = state.copyWith(inquiries: previousInquiries, errorMessage: ApiClient.formatError(e));
      return false;
    }
  }

  /// Trigger live sync of comments & inquiries from LinkedIn
  Future<void> syncLinkedIn() async {
    state = state.copyWith(isSyncing: true, clearMessages: true);
    try {
      final response = await _api.dio.post(ApiEndpoints.syncLinkedIn);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final count = response.data['new_items'] ?? response.data['data']?['new_items'] ?? 0;
        state = state.copyWith(
          isSyncing: false,
          successMessage: 'LinkedIn sync complete ($count new inquiries fetched)',
        );
        await fetchInquiries();
      } else {
        state = state.copyWith(
          isSyncing: false,
          errorMessage: response.data['message'] ?? 'LinkedIn sync failed.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isSyncing: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  void setFilterJob(int? jobId) {
    if (jobId == null) {
      state = state.copyWith(clearSelectedJob: true);
    } else {
      state = state.copyWith(selectedJobId: jobId);
    }
    fetchInquiries(jobId: jobId);
  }

  void setPlatformFilter(String platform) {
    state = state.copyWith(selectedPlatform: platform);
    fetchInquiries(platform: platform);
  }

  void setStatusFilter(String status) {
    state = state.copyWith(selectedStatus: status);
    fetchInquiries(status: status);
  }
}

final companyInquiriesProvider =
    StateNotifierProvider<CompanyInquiriesNotifier, CompanyInquiriesState>((ref) {
  return CompanyInquiriesNotifier();
});
