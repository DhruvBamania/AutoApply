import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/inquiry_model.dart';
import '../models/job_model.dart';

/// State for the Gemini AI Matching & Leaderboard Screen
class AiMatchingState {
  final bool isLoading;
  final bool isMatching;
  final bool isShortlisting;
  final JobModel? job;
  final List<InquiryModel> leaderboard;
  final Set<int> selectedForBatch;
  final String? errorMessage;
  final String? successMessage;

  const AiMatchingState({
    this.isLoading = false,
    this.isMatching = false,
    this.isShortlisting = false,
    this.job,
    this.leaderboard = const [],
    this.selectedForBatch = const {},
    this.errorMessage,
    this.successMessage,
  });

  AiMatchingState copyWith({
    bool? isLoading,
    bool? isMatching,
    bool? isShortlisting,
    JobModel? job,
    List<InquiryModel>? leaderboard,
    Set<int>? selectedForBatch,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return AiMatchingState(
      isLoading: isLoading ?? this.isLoading,
      isMatching: isMatching ?? this.isMatching,
      isShortlisting: isShortlisting ?? this.isShortlisting,
      job: job ?? this.job,
      leaderboard: leaderboard ?? this.leaderboard,
      selectedForBatch: selectedForBatch ?? this.selectedForBatch,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class AiMatchingNotifier extends StateNotifier<AiMatchingState> {
  AiMatchingNotifier() : super(const AiMatchingState());

  final _api = ApiClient();

  /// 1. Load existing AI ranking leaderboard for this job (Stale-While-Revalidate)
  Future<void> fetchLeaderboard(int jobId) async {
    final isCached = state.job?.id == jobId && state.leaderboard.isNotEmpty;
    if (!isCached) {
      state = state.copyWith(isLoading: true, clearMessages: true);
    } else {
      state = state.copyWith(clearMessages: true);
    }
    try {
      final response = await _api.dio.get(ApiEndpoints.companyJobLeaderboard(jobId));
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final jobData = response.data['job'] ?? response.data['data']?['job'];
        final job = jobData != null ? JobModel.fromJson(Map<String, dynamic>.from(jobData)) : null;
        final list = (response.data['leaderboard'] ??
            response.data['candidates'] ??
            response.data['data']?['leaderboard'] ??
            response.data['data']?['candidates'] ??
            []) as List;
        final leaderboard = list.map((e) => InquiryModel.fromJson(Map<String, dynamic>.from(e))).toList();

        // Sort by rank or score descending
        leaderboard.sort((a, b) => (b.aiMatchScore ?? 0).compareTo(a.aiMatchScore ?? 0));

        state = state.copyWith(
          isLoading: false,
          job: job,
          leaderboard: leaderboard,
          selectedForBatch: {},
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load AI matches.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// 2. Run Gemini AI matching on all candidate resumes for this job
  Future<bool> runAiMatch(int jobId) async {
    state = state.copyWith(isMatching: true, clearMessages: true);
    try {
      final response = await _api.dio.post(ApiEndpoints.jobMatchCandidates(jobId));
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['leaderboard'] ??
            response.data['candidates'] ??
            response.data['data']?['leaderboard'] ??
            response.data['data']?['candidates'] ??
            []) as List;
        final leaderboard = list.map((e) => InquiryModel.fromJson(Map<String, dynamic>.from(e))).toList();

        leaderboard.sort((a, b) => (b.aiMatchScore ?? 0).compareTo(a.aiMatchScore ?? 0));

        state = state.copyWith(
          isMatching: false,
          leaderboard: leaderboard,
          successMessage: 'Gemini AI evaluated and ranked all candidates!',
        );
        return true;
      } else {
        state = state.copyWith(
          isMatching: false,
          errorMessage: response.data['message'] ?? 'AI matching failed.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isMatching: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Toggle selection checkbox for batch shortlisting
  void toggleSelection(int inquiryId) {
    final updated = Set<int>.from(state.selectedForBatch);
    if (updated.contains(inquiryId)) {
      updated.remove(inquiryId);
    } else {
      updated.add(inquiryId);
    }
    state = state.copyWith(selectedForBatch: updated);
  }

  /// Select all candidates scoring above a certain threshold (e.g. 75%)
  void selectTopCandidates(double minScore) {
    final selected = state.leaderboard
        .where((c) => (c.aiMatchScore ?? 0) >= minScore)
        .map((c) => c.id)
        .toSet();
    state = state.copyWith(selectedForBatch: selected);
  }

  /// 3. Batch shortlist all selected candidates (Optimistic UI: updates immediately, rolls back on error)
  Future<bool> batchShortlist(int jobId) async {
    if (state.selectedForBatch.isEmpty) return false;

    final count = state.selectedForBatch.length;
    final previousLeaderboard = state.leaderboard;
    final previousSelected = state.selectedForBatch;

    // Optimistically mark as shortlisted immediately
    final updatedLeaderboard = state.leaderboard.map((item) {
      if (previousSelected.contains(item.id)) {
        return item.copyWith(status: 'shortlisted');
      }
      return item;
    }).toList();

    state = state.copyWith(
      isShortlisting: true,
      leaderboard: updatedLeaderboard,
      selectedForBatch: {},
      clearMessages: true,
    );

    try {
      final response = await _api.dio.post(
        ApiEndpoints.batchShortlistCandidates(jobId),
        data: {'inquiry_ids': previousSelected.toList()},
      );

      if (response.data['status'] == 'success' || response.data['success'] == true) {
        state = state.copyWith(
          isShortlisting: false,
          successMessage: 'Successfully shortlisted $count candidate(s)!',
        );
        return true;
      } else {
        // Rollback on failure
        state = state.copyWith(
          isShortlisting: false,
          leaderboard: previousLeaderboard,
          selectedForBatch: previousSelected,
          errorMessage: response.data['message'] ?? 'Failed to shortlist.',
        );
        return false;
      }
    } catch (e) {
      // Rollback on error
      state = state.copyWith(
        isShortlisting: false,
        leaderboard: previousLeaderboard,
        selectedForBatch: previousSelected,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// 4. Shortlist an individual candidate from the AI leaderboard with schedule details
  Future<bool> shortlistCandidate(
    int inquiryId, {
    DateTime? scheduledAt,
    String? meetingType,
    String? meetingLink,
    String? notes,
    bool sendEmail = true,
  }) async {
    final previousLeaderboard = state.leaderboard;
    final index = previousLeaderboard.indexWhere((inq) => inq.id == inquiryId);
    if (index == -1) return false;

    // Optimistic update
    final optimisticList = [...previousLeaderboard];
    optimisticList[index] = previousLeaderboard[index].copyWith(
      status: 'shortlisted',
      interviewScheduledAt: scheduledAt?.toIso8601String(),
      meetingType: meetingType,
      meetingLinkOrLocation: meetingLink,
      interviewNotes: notes,
    );
    state = state.copyWith(
      leaderboard: optimisticList,
      successMessage: sendEmail ? 'Candidate shortlisted & interview invite sent!' : 'Candidate shortlisted!',
    );

    final payload = <String, dynamic>{
      'status': 'shortlisted',
      if (scheduledAt != null) 'interview_scheduled_at': scheduledAt.toIso8601String(),
      'meeting_type': ?meetingType,
      if (meetingLink != null && meetingLink.isNotEmpty) 'meeting_link_or_location': meetingLink,
      if (notes != null && notes.isNotEmpty) 'interview_notes': notes,
      'send_email': sendEmail,
    };

    try {
      final response = await _api.dio.patch(
        ApiEndpoints.updateInquiryStatus(inquiryId),
        data: payload,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (!isSuccess) {
        state = state.copyWith(leaderboard: previousLeaderboard, errorMessage: 'Failed to shortlist candidate.');
        return false;
      }

      final rawInquiry = response.data['inquiry'] ?? response.data['data']?['inquiry'];
      if (rawInquiry != null) {
        final serverInquiry = InquiryModel.fromJson(Map<String, dynamic>.from(rawInquiry));
        final updated = state.leaderboard.map((inq) {
          return inq.id == inquiryId ? serverInquiry : inq;
        }).toList();
        state = state.copyWith(leaderboard: updated);
      }

      return true;
    } catch (e) {
      state = state.copyWith(leaderboard: previousLeaderboard, errorMessage: ApiClient.formatError(e));
      return false;
    }
  }
}

final aiMatchingProvider =
    StateNotifierProvider<AiMatchingNotifier, AiMatchingState>((ref) {
  return AiMatchingNotifier();
});
