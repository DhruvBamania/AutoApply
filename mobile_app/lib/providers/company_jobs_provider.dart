import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/job_model.dart';

/// CompanyJobsState: Holds the company's active and archived job postings
class CompanyJobsState {
  final bool isLoading;
  final bool isSubmitting;
  final List<JobModel> jobs;
  final String? errorMessage;
  final String? successMessage;

  const CompanyJobsState({
    this.isLoading = false,
    this.isSubmitting = false,
    this.jobs = const [],
    this.errorMessage,
    this.successMessage,
  });

  CompanyJobsState copyWith({
    bool? isLoading,
    bool? isSubmitting,
    List<JobModel>? jobs,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return CompanyJobsState(
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      jobs: jobs ?? this.jobs,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class CompanyJobsNotifier extends StateNotifier<CompanyJobsState> {
  CompanyJobsNotifier() : super(const CompanyJobsState());

  final _api = ApiClient();

  /// Load all job postings created by this company (Stale-While-Revalidate)
  Future<void> fetchJobs() async {
    // Only show full loading spinner if cache is completely empty
    if (state.jobs.isEmpty) {
      state = state.copyWith(isLoading: true, clearMessages: true);
    } else {
      state = state.copyWith(clearMessages: true);
    }
    try {
      final response = await _api.dio.get(ApiEndpoints.companyJobs);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['jobs'] ?? response.data['data']?['jobs'] ?? []) as List;
        final jobs = list.map((item) => JobModel.fromJson(Map<String, dynamic>.from(item))).toList();
        state = state.copyWith(jobs: jobs, isLoading: false);
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load jobs.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// Create & Multi-post a job across LinkedIn, Naukri, Indeed
  /// Supports optional flyer poster upload
  Future<bool> createJob({
    required String title,
    required String companyName,
    required String location,
    required String jobType,
    required String experienceLevel,
    required String salaryRange,
    required String description,
    required String requirements,
    required List<String> platforms,
    String? flyerFilePath,
    String? flyerFileName,
    List<int>? flyerFileBytes,
  }) async {
    state = state.copyWith(isSubmitting: true, clearMessages: true);
    try {
      // Build form data to support file upload
      final Map<String, dynamic> formMap = {
        'title': title,
        'company_name': companyName,
        'location': location,
        'job_type': jobType,
        'experience_level': experienceLevel,
        'salary_range': salaryRange,
        'description': description,
        'requirements': requirements,
      };

      // Add platforms array
      for (int i = 0; i < platforms.length; i++) {
        formMap['platforms[$i]'] = platforms[i];
      }

      // Attach flyer image if selected
      if (flyerFileBytes != null && flyerFileName != null) {
        formMap['flyer_image'] = MultipartFile.fromBytes(
          flyerFileBytes,
          filename: flyerFileName,
        );
      } else if (flyerFilePath != null && flyerFilePath.isNotEmpty) {
        formMap['flyer_image'] = await MultipartFile.fromFile(
          flyerFilePath,
          filename: flyerFileName,
        );
      }

      final formData = FormData.fromMap(formMap);

      final response = await _api.dio.post(
        ApiEndpoints.companyJobs,
        data: formData,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 201 ||
          response.statusCode == 200;

      if (isSuccess) {
        final jobData = response.data['job'] ?? response.data['data']?['job'];
        if (jobData != null) {
          final newJob = JobModel.fromJson(Map<String, dynamic>.from(jobData));
          state = state.copyWith(
            isSubmitting: false,
            jobs: [newJob, ...state.jobs],
            successMessage: 'Job posted successfully across selected platforms!',
          );
          return true;
        }
      }
      state = state.copyWith(
        isSubmitting: false,
        errorMessage: response.data['message'] ?? 'Failed to create job.',
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        isSubmitting: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Repost an existing job to fresh dates
  Future<bool> repostJob(int jobId) async {
    try {
      final response = await _api.dio.post(ApiEndpoints.repostJob(jobId));
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;
      if (isSuccess) {
        await fetchJobs();
        return true;
      }
      return false;
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
      return false;
    }
  }

  /// Delete a job posting (Optimistic UI: removes instantly, reverts on failure)
  Future<bool> deleteJob(int jobId) async {
    final previousJobs = state.jobs;
    // Optimistically remove from state immediately
    state = state.copyWith(
      jobs: state.jobs.where((j) => j.id != jobId).toList(),
      clearMessages: true,
    );

    try {
      final response = await _api.dio.delete(ApiEndpoints.deleteJob(jobId));
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;
      if (isSuccess) {
        state = state.copyWith(
          successMessage: 'Job posting deleted.',
        );
        return true;
      } else {
        // Rollback
        state = state.copyWith(
          jobs: previousJobs,
          errorMessage: response.data['message'] ?? 'Failed to delete job.',
        );
        return false;
      }
    } catch (e) {
      // Rollback
      state = state.copyWith(
        jobs: previousJobs,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }
}

final companyJobsProvider =
    StateNotifierProvider<CompanyJobsNotifier, CompanyJobsState>((ref) {
  return CompanyJobsNotifier();
});
