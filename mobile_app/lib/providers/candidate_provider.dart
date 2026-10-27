import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/job_model.dart';

/// Model representing a Candidate's uploaded resume
class CandidateResumeModel {
  final int id;
  final String fileName;
  final String? originalName;
  final String? extractedText;
  final String createdAt;

  CandidateResumeModel({
    required this.id,
    required this.fileName,
    this.originalName,
    this.extractedText,
    required this.createdAt,
  });

  factory CandidateResumeModel.fromJson(Map<String, dynamic> json) {
    return CandidateResumeModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      fileName: json['file_name'] ?? json['file_path'] ?? 'Resume',
      originalName: json['original_name'],
      extractedText: json['extracted_text'] ?? json['resume_text'],
      createdAt: json['created_at']?.toString() ?? '',
    );
  }
}

/// Model representing an ATS Tailored Resume
class TailoredResumeModel {
  final int id;
  final int? jobPostingId;
  final String tailoredText;
  final double atsScore;
  final List<String> matchedKeywords;
  final List<String> suggestions;
  final String? templateId;

  TailoredResumeModel({
    required this.id,
    this.jobPostingId,
    required this.tailoredText,
    required this.atsScore,
    this.matchedKeywords = const [],
    this.suggestions = const [],
    this.templateId,
  });

  factory TailoredResumeModel.fromJson(Map<String, dynamic> json) {
    List<String> toList(dynamic v) {
      if (v is List) return v.map((e) => e.toString()).toList();
      return [];
    }

    return TailoredResumeModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      jobPostingId: json['job_posting_id'] != null ? int.tryParse(json['job_posting_id'].toString()) : null,
      tailoredText: json['tailored_text'] ?? json['content'] ?? '',
      atsScore: (json['ats_score'] is num) ? (json['ats_score'] as num).toDouble() : 0.0,
      matchedKeywords: toList(json['matched_keywords']),
      suggestions: toList(json['suggestions']),
      templateId: json['template_id']?.toString(),
    );
  }
}

/// Model for Resume Template gallery
class ResumeTemplateModel {
  final String id;
  final String name;
  final String category;
  final String description;
  final String? previewUrl;

  ResumeTemplateModel({
    required this.id,
    required this.name,
    required this.category,
    required this.description,
    this.previewUrl,
  });

  factory ResumeTemplateModel.fromJson(Map<String, dynamic> json) {
    return ResumeTemplateModel(
      id: json['id']?.toString() ?? '',
      name: json['name'] ?? '',
      category: json['category'] ?? 'Professional',
      description: json['description'] ?? '',
      previewUrl: json['preview_url'] ?? json['preview_image'],
    );
  }
}

/// State for Candidate features
class CandidateState {
  final bool isLoadingJobs;
  final bool isSubmittingApplication;
  final bool isTailoring;
  final List<JobModel> publicJobs;
  final List<CandidateResumeModel> resumes;
  final List<ResumeTemplateModel> templates;
  final TailoredResumeModel? latestTailored;
  final String? errorMessage;
  final String? successMessage;

  const CandidateState({
    this.isLoadingJobs = false,
    this.isSubmittingApplication = false,
    this.isTailoring = false,
    this.publicJobs = const [],
    this.resumes = const [],
    this.templates = const [],
    this.latestTailored,
    this.errorMessage,
    this.successMessage,
  });

  CandidateState copyWith({
    bool? isLoadingJobs,
    bool? isSubmittingApplication,
    bool? isTailoring,
    List<JobModel>? publicJobs,
    List<CandidateResumeModel>? resumes,
    List<ResumeTemplateModel>? templates,
    TailoredResumeModel? latestTailored,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return CandidateState(
      isLoadingJobs: isLoadingJobs ?? this.isLoadingJobs,
      isSubmittingApplication: isSubmittingApplication ?? this.isSubmittingApplication,
      isTailoring: isTailoring ?? this.isTailoring,
      publicJobs: publicJobs ?? this.publicJobs,
      resumes: resumes ?? this.resumes,
      templates: templates ?? this.templates,
      latestTailored: latestTailored ?? this.latestTailored,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class CandidateNotifier extends StateNotifier<CandidateState> {
  CandidateNotifier() : super(const CandidateState());

  final _api = ApiClient();

  /// Discover all active jobs (Stale-While-Revalidate)
  Future<void> fetchPublicJobs({String? search, String? location, String? jobType}) async {
    final isFiltered = (search != null && search.isNotEmpty) ||
        (location != null && location.isNotEmpty) ||
        (jobType != null && jobType.isNotEmpty);

    // Only show full loading spinner if cache is empty or if applying a filter
    if (state.publicJobs.isEmpty || isFiltered) {
      state = state.copyWith(isLoadingJobs: true, clearMessages: true);
    } else {
      state = state.copyWith(clearMessages: true);
    }
    try {
      final queryParams = <String, dynamic>{};
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (location != null && location.isNotEmpty) queryParams['location'] = location;
      if (jobType != null && jobType.isNotEmpty) queryParams['job_type'] = jobType;

      final response = await _api.dio.get(
        ApiEndpoints.publicJobs,
        queryParameters: queryParams,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        dynamic rawJobs = response.data['jobs'] ?? response.data['data']?['jobs'];
        List list = [];
        if (rawJobs is List) {
          list = rawJobs;
        } else if (rawJobs is Map && rawJobs['data'] is List) {
          list = rawJobs['data'] as List;
        }
        final jobs = list.map((e) => JobModel.fromJson(Map<String, dynamic>.from(e))).toList();
        state = state.copyWith(publicJobs: jobs, isLoadingJobs: false);
      } else {
        state = state.copyWith(
          isLoadingJobs: false,
          errorMessage: response.data['message'] ?? 'Failed to load jobs.',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoadingJobs: false,
        errorMessage: ApiClient.formatError(e),
      );
    }
  }

  /// 1-Tap Apply or submit Inquiry to a Job
  Future<bool> applyToJob({
    required int jobId,
    required String name,
    required String email,
    String? phone,
    String? headline,
    required String message,
    required String type, // 'application' or 'query'
    String? resumeFilePath,
    String? resumeFileName,
    List<int>? resumeFileBytes,
  }) async {
    state = state.copyWith(isSubmittingApplication: true, clearMessages: true);
    try {
      final formMap = <String, dynamic>{
        'candidate_name': name,
        'candidate_email': email,
        'message': message,
        'type': type,
        'platform': 'direct',
      };
      if (phone != null) formMap['candidate_phone'] = phone;
      if (headline != null) formMap['candidate_headline'] = headline;

      if (resumeFileBytes != null && resumeFileName != null) {
        formMap['resume'] = MultipartFile.fromBytes(
          resumeFileBytes,
          filename: resumeFileName,
        );
      } else if (resumeFilePath != null && resumeFilePath.isNotEmpty) {
        formMap['resume'] = await MultipartFile.fromFile(
          resumeFilePath,
          filename: resumeFileName,
        );
      }

      final formData = FormData.fromMap(formMap);
      final response = await _api.dio.post(
        ApiEndpoints.applyToJob(jobId),
        data: formData,
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 201 ||
          response.statusCode == 200;

      if (isSuccess) {
        state = state.copyWith(
          isSubmittingApplication: false,
          successMessage: 'Application submitted successfully!',
        );
        return true;
      } else {
        state = state.copyWith(
          isSubmittingApplication: false,
          errorMessage: response.data['message'] ?? 'Failed to submit application.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isSubmittingApplication: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Candidate sends direct WhatsApp message via Twilio API
  Future<bool> sendWhatsAppInquiry({
    required int jobId,
    required String name,
    required String phone,
    String? email,
    String? headline,
    required String message,
  }) async {
    state = state.copyWith(isSubmittingApplication: true, clearMessages: true);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.jobWhatsAppMessage(jobId),
        data: {
          'candidate_name': name,
          'candidate_phone': phone,
          'candidate_email': email,
          'candidate_headline': headline,
          'message': message,
        },
      );

      final isSuccess = response.data['success'] == true || response.statusCode == 201;

      if (isSuccess) {
        state = state.copyWith(
          isSubmittingApplication: false,
          successMessage: 'WhatsApp message sent to hiring team!',
        );
        return true;
      } else {
        state = state.copyWith(
          isSubmittingApplication: false,
          errorMessage: response.data['error'] ?? response.data['message'] ?? 'Failed to send WhatsApp message.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isSubmittingApplication: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Fetch candidate's saved resumes
  Future<void> fetchResumes() async {
    try {
      final response = await _api.dio.get(ApiEndpoints.candidateResumes);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final raw = response.data['resumes'] ??
            response.data['data']?['resumes'] ??
            (response.data['resume'] != null ? [response.data['resume']] : []);
        final list = raw as List;
        final resumes = list.map((e) => CandidateResumeModel.fromJson(Map<String, dynamic>.from(e))).toList();
        state = state.copyWith(resumes: resumes);
      }
    } catch (e) {
      state = state.copyWith(errorMessage: ApiClient.formatError(e));
    }
  }

  /// Fetch 15 resume templates gallery
  Future<void> fetchTemplates() async {
    try {
      final response = await _api.dio.get(ApiEndpoints.resumeTemplates);
      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final list = (response.data['templates'] ?? response.data['data']?['templates'] ?? []) as List;
        final templates = list.map((e) => ResumeTemplateModel.fromJson(Map<String, dynamic>.from(e))).toList();
        state = state.copyWith(templates: templates);
      }
    } catch (_) {}
  }

  /// Trigger Gemini AI ATS resume tailoring
  Future<bool> tailorResume({
    required int resumeId,
    required String targetJobRole,
    required String targetJobDescription,
    String? templateId,
  }) async {
    state = state.copyWith(isTailoring: true, clearMessages: true);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.tailorResume,
        data: {
          'resume_id': resumeId,
          'target_job_role': targetJobRole,
          'target_job_description': targetJobDescription,
          'template_id': ?templateId,
        },
      );

      final isSuccess = response.data['success'] == true ||
          response.data['status'] == 'success' ||
          response.statusCode == 200;

      if (isSuccess) {
        final rawTailored = response.data['tailored_resume'] ?? response.data['data']?['tailored_resume'];
        if (rawTailored != null) {
          final tailored = TailoredResumeModel.fromJson(Map<String, dynamic>.from(rawTailored));
          state = state.copyWith(
            isTailoring: false,
            latestTailored: tailored,
            successMessage: 'Resume successfully tailored for ATS by Gemini AI!',
          );
          return true;
        }
      }
      state = state.copyWith(
        isTailoring: false,
        errorMessage: response.data['message'] ?? 'Tailoring failed.',
      );
      return false;
    } catch (e) {
      state = state.copyWith(
        isTailoring: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }
}

final candidateProvider =
    StateNotifierProvider<CandidateNotifier, CandidateState>((ref) {
  return CandidateNotifier();
});
