import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/interview_prep_model.dart';

class InterviewPrepState {
  final bool isLoading;
  final bool isGenerating;
  final List<InterviewPrepModel> preps;
  final InterviewPrepModel? activePrep;
  final Map<String, dynamic>? resumeProfile;
  final String? errorMessage;
  final String? successMessage;

  const InterviewPrepState({
    this.isLoading = false,
    this.isGenerating = false,
    this.preps = const [],
    this.activePrep,
    this.resumeProfile,
    this.errorMessage,
    this.successMessage,
  });

  InterviewPrepState copyWith({
    bool? isLoading,
    bool? isGenerating,
    List<InterviewPrepModel>? preps,
    InterviewPrepModel? activePrep,
    Map<String, dynamic>? resumeProfile,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return InterviewPrepState(
      isLoading: isLoading ?? this.isLoading,
      isGenerating: isGenerating ?? this.isGenerating,
      preps: preps ?? this.preps,
      activePrep: activePrep ?? this.activePrep,
      resumeProfile: resumeProfile ?? this.resumeProfile,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class InterviewPrepNotifier extends StateNotifier<InterviewPrepState> {
  InterviewPrepNotifier() : super(const InterviewPrepState());

  final _api = ApiClient();

  /// 1. Fetch all saved interview preparation guides
  Future<void> fetchPreps() async {
    state = state.copyWith(isLoading: true, clearMessages: true);
    try {
      final response = await _api.dio.get(ApiEndpoints.interviewPreps);
      final isSuccess = response.data['success'] == true || response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['data'] as List? ?? [])
            .map((e) => InterviewPrepModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        final currentActive = state.activePrep != null
            ? list.firstWhere((p) => p.id == state.activePrep!.id, orElse: () => list.first)
            : (list.isNotEmpty ? list.first : null);

        state = state.copyWith(
          isLoading: false,
          preps: list,
          activePrep: currentActive,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load interview preparations.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// 2. Fetch candidate profile from resume for instant 1-tap auto-fill
  Future<Map<String, dynamic>?> fetchResumeProfile() async {
    try {
      final response = await _api.dio.get(ApiEndpoints.interviewPrepProfile);
      if (response.data['success'] == true && response.data['profile'] != null) {
        final profile = Map<String, dynamic>.from(response.data['profile']);
        state = state.copyWith(resumeProfile: profile);
        return profile;
      }
    } catch (e) {
      // Non-critical, ignore
    }
    return null;
  }

  /// 3. Generate dynamic interview questions via AI
  Future<bool> generatePrep({
    required String roleTitle,
    required String experienceLevel,
    required String techStack,
    bool useResume = true,
  }) async {
    state = state.copyWith(isGenerating: true, clearMessages: true);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.generateInterviewPrep,
        data: {
          'role_title': roleTitle,
          'experience_level': experienceLevel,
          'tech_stack': techStack,
          'use_resume': useResume,
        },
      );

      final isSuccess = response.data['success'] == true || response.statusCode == 200;

      if (isSuccess && response.data['data'] != null) {
        final newPrep = InterviewPrepModel.fromJson(Map<String, dynamic>.from(response.data['data']));
        final updatedList = [newPrep, ...state.preps.where((p) => p.id != newPrep.id)];

        state = state.copyWith(
          isGenerating: false,
          preps: updatedList,
          activePrep: newPrep,
          successMessage: response.data['message'] ?? 'Interview questions generated successfully!',
        );
        return true;
      } else {
        state = state.copyWith(
          isGenerating: false,
          errorMessage: response.data['message'] ?? 'Failed to generate questions.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isGenerating: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// 4. Select active prep guide from library
  void selectPrep(InterviewPrepModel prep) {
    state = state.copyWith(activePrep: prep);
  }

  /// 5. Toggle bookmark on a specific question with optimistic UI update
  Future<void> toggleBookmark(int prepId, String questionId) async {
    if (state.activePrep == null || state.activePrep!.id != prepId) return;

    final currentPrep = state.activePrep!;
    final currentBookmarks = List<String>.from(currentPrep.bookmarkedIds);
    final isCurrentlyBookmarked = currentBookmarks.contains(questionId);

    if (isCurrentlyBookmarked) {
      currentBookmarks.remove(questionId);
    } else {
      currentBookmarks.add(questionId);
    }

    // Optimistically update topic question models
    final updatedTopics = currentPrep.topics.map((topic) {
      final updatedQuestions = topic.questions.map((q) {
        if (q.id == questionId) {
          return q.copyWith(isBookmarked: !isCurrentlyBookmarked);
        }
        return q;
      }).toList();
      return InterviewTopicModel(
        topicName: topic.topicName,
        description: topic.description,
        questions: updatedQuestions,
      );
    }).toList();

    final updatedPrep = currentPrep.copyWith(
      bookmarkedIds: currentBookmarks,
      topics: updatedTopics,
    );

    final updatedPrepsList = state.preps.map((p) => p.id == prepId ? updatedPrep : p).toList();

    state = state.copyWith(
      activePrep: updatedPrep,
      preps: updatedPrepsList,
    );

    try {
      await _api.dio.post(
        ApiEndpoints.toggleBookmarkPrep(prepId),
        data: {'question_id': questionId},
      );
    } catch (_) {
      // Silently fail or rollback if needed
    }
  }

  /// 6. Delete a preparation guide
  Future<bool> deletePrep(int prepId) async {
    try {
      final response = await _api.dio.delete(ApiEndpoints.deleteInterviewPrep(prepId));
      if (response.data['success'] == true || response.statusCode == 200) {
        final remaining = state.preps.where((p) => p.id != prepId).toList();
        final newActive = state.activePrep?.id == prepId
            ? (remaining.isNotEmpty ? remaining.first : null)
            : state.activePrep;

        state = state.copyWith(
          preps: remaining,
          activePrep: newActive,
          successMessage: 'Interview preparation guide removed.',
        );
        return true;
      }
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
    }
    return false;
  }
}

final interviewPrepProvider =
    StateNotifierProvider<InterviewPrepNotifier, InterviewPrepState>((ref) {
  return InterviewPrepNotifier();
});
