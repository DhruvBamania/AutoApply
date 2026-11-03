import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/multilingual_resume_model.dart';

class MultilingualResumeState {
  final bool isLoading;
  final bool isTranslating;
  final bool hasBaseResume;
  final List<MultilingualResumeModel> resumes;
  final List<Map<String, dynamic>> presets;
  final String? errorMessage;
  final String? successMessage;

  const MultilingualResumeState({
    this.isLoading = false,
    this.isTranslating = false,
    this.hasBaseResume = true,
    this.resumes = const [],
    this.presets = const [],
    this.errorMessage,
    this.successMessage,
  });

  MultilingualResumeState copyWith({
    bool? isLoading,
    bool? isTranslating,
    bool? hasBaseResume,
    List<MultilingualResumeModel>? resumes,
    List<Map<String, dynamic>>? presets,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return MultilingualResumeState(
      isLoading: isLoading ?? this.isLoading,
      isTranslating: isTranslating ?? this.isTranslating,
      hasBaseResume: hasBaseResume ?? this.hasBaseResume,
      resumes: resumes ?? this.resumes,
      presets: presets ?? this.presets,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class MultilingualResumeNotifier extends StateNotifier<MultilingualResumeState> {
  MultilingualResumeNotifier() : super(const MultilingualResumeState());

  final _api = ApiClient();

  /// 1. Fetch user's translated resumes and supported language presets
  Future<void> fetchResumes() async {
    state = state.copyWith(isLoading: true, clearMessages: true);
    try {
      final response = await _api.dio.get(ApiEndpoints.candidateMultilingualResumes);
      final isSuccess = response.data['success'] == true || response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['resumes'] as List? ?? [])
            .map((e) => MultilingualResumeModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        final presets = (response.data['presets'] as List? ?? [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();

        state = state.copyWith(
          isLoading: false,
          resumes: list,
          presets: presets,
          hasBaseResume: response.data['has_base_resume'] == true,
        );
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['error']?.toString() ?? 'Failed to load multilingual resumes.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// 2. Translate resume into one or more target languages
  Future<bool> translateResume({
    required List<String> languages,
    int templateId = 1,
    String? targetRole,
  }) async {
    if (languages.isEmpty) {
      state = state.copyWith(errorMessage: 'Please select at least one language.');
      return false;
    }

    state = state.copyWith(isTranslating: true, clearMessages: true);
    try {
      final payload = <String, dynamic>{
        'languages': languages,
        'template_id': templateId,
      };
      if (targetRole != null && targetRole.trim().isNotEmpty) {
        payload['target_role'] = targetRole.trim();
      }

      final response = await _api.dio.post(
        ApiEndpoints.candidateMultilingualTranslate,
        data: payload,
      );

      final isSuccess = response.data['success'] == true || response.statusCode == 200;
      if (isSuccess) {
        // Refresh full list after translation
        await fetchResumes();
        state = state.copyWith(
          isTranslating: false,
          successMessage: 'Successfully translated resume into ${languages.length} language(s)!',
        );
        return true;
      } else {
        state = state.copyWith(
          isTranslating: false,
          errorMessage: response.data['error']?.toString() ?? 'Translation failed.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isTranslating: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// 3. Fetch single resume details (with full structured resumeData)
  Future<MultilingualResumeModel?> fetchResumeDetail(int id) async {
    try {
      final response = await _api.dio.get(ApiEndpoints.candidateMultilingualResumeDetail(id));
      if (response.data['success'] == true && response.data['data'] != null) {
        return MultilingualResumeModel.fromJson(Map<String, dynamic>.from(response.data['data']));
      }
    } catch (e) {
      // Non-critical error
    }
    return null;
  }

  /// 4. Delete a translated resume
  Future<bool> deleteResume(int id) async {
    try {
      final response = await _api.dio.delete(ApiEndpoints.deleteCandidateMultilingualResume(id));
      if (response.data['success'] == true || response.statusCode == 200) {
        final remaining = state.resumes.where((r) => r.id != id).toList();
        state = state.copyWith(
          resumes: remaining,
          successMessage: 'Translated resume removed from vault.',
        );
        return true;
      }
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
    }
    return false;
  }
}

final multilingualResumeProvider =
    StateNotifierProvider<MultilingualResumeNotifier, MultilingualResumeState>((ref) {
  return MultilingualResumeNotifier();
});

