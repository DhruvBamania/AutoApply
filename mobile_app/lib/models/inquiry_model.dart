/// InquiryModel: Represents a candidate query, comment, or job application.
/// 
/// In Flutter & Dart, models are "data blueprints". 
/// When our Laravel backend sends JSON like:
///   {"id": 1, "candidate_name": "Aarav Sharma", "is_pinned": true, ...}
/// the `fromJson` factory method converts that JSON into this typed Dart object.
library;

class InquiryModel {
  final int id;
  final int jobPostingId;
  final String? jobTitle;
  final String candidateName;
  final String candidateEmail;
  final String? candidatePhone;
  final String? candidateHeadline;
  final String platform; // 'linkedin', 'naukri', 'indeed', 'direct'
  final String type; // 'application', 'comment', 'query'
  final String message;
  final String? resumePath;
  final String status; // 'new', 'reviewed', 'shortlisted', 'rejected'
  final bool isPinned;
  final String? pinnedAt;
  final String? employerReply;
  final String? repliedAt;
  final String createdAt;

  // Interview Scheduling fields
  final String? interviewScheduledAt;
  final String? meetingType;
  final String? meetingLinkOrLocation;
  final String? interviewNotes;

  // AI Matching and Scoring fields (from Gemini AI matching)
  final double? aiMatchScore;
  final int? aiRank;
  final double? aiExperienceScore;
  final double? aiSkillsScore;
  final double? aiProjectsScore;
  final String? aiRecommendation;
  final String? aiSummary;
  final List<String> aiMatchedSkills;
  final List<String> aiMissingSkills;

  InquiryModel({
    required this.id,
    required this.jobPostingId,
    this.jobTitle,
    required this.candidateName,
    required this.candidateEmail,
    this.candidatePhone,
    this.candidateHeadline,
    required this.platform,
    required this.type,
    required this.message,
    this.resumePath,
    required this.status,
    required this.isPinned,
    this.pinnedAt,
    this.employerReply,
    this.repliedAt,
    required this.createdAt,
    this.interviewScheduledAt,
    this.meetingType,
    this.meetingLinkOrLocation,
    this.interviewNotes,
    this.aiMatchScore,
    this.aiRank,
    this.aiExperienceScore,
    this.aiSkillsScore,
    this.aiProjectsScore,
    this.aiRecommendation,
    this.aiSummary,
    this.aiMatchedSkills = const [],
    this.aiMissingSkills = const [],
  });

  /// Factory constructor to safely parse JSON returned by our Laravel REST API.
  factory InquiryModel.fromJson(Map<String, dynamic> json) {
    // Helper to safely parse numbers into double
    double? parseDouble(dynamic value) {
      if (value == null) return null;
      if (value is num) return value.toDouble();
      return double.tryParse(value.toString());
    }

    // Helper to parse string lists (e.g. matched skills)
    List<String> parseList(dynamic value) {
      if (value == null) return [];
      if (value is List) {
        return value.map((e) => e.toString()).toList();
      }
      return [];
    }

    final parsedId = json['id'] ?? json['inquiry_id'] ?? 0;
    return InquiryModel(
      id: parsedId is int ? parsedId : int.tryParse(parsedId.toString()) ?? 0,
      jobPostingId: json['job_posting_id'] is int
          ? json['job_posting_id']
          : int.tryParse(json['job_posting_id']?.toString() ?? '0') ?? 0,
      jobTitle: json['job_posting']?['title'] ?? json['job_title'],
      candidateName: json['candidate_name'] ?? 'Candidate',
      candidateEmail: json['candidate_email'] ?? '',
      candidatePhone: json['candidate_phone'],
      candidateHeadline: json['candidate_headline'],
      platform: json['platform'] ?? 'direct',
      type: json['type'] ?? 'query',
      message: json['message'] ?? '',
      resumePath: json['resume_path'],
      status: json['status'] ?? 'new',
      isPinned: json['is_pinned'] == true || json['is_pinned'] == 1,
      pinnedAt: json['pinned_at'],
      employerReply: json['employer_reply'],
      repliedAt: json['replied_at'],
      createdAt: json['created_at']?.toString() ?? '',
      interviewScheduledAt: json['interview_scheduled_at']?.toString(),
      meetingType: json['meeting_type']?.toString(),
      meetingLinkOrLocation: json['meeting_link_or_location']?.toString(),
      interviewNotes: json['interview_notes']?.toString(),
      aiMatchScore: parseDouble(json['ai_match_score'] ?? json['match_score']),
      aiRank: (json['ai_rank'] ?? json['rank']) != null
          ? int.tryParse((json['ai_rank'] ?? json['rank']).toString())
          : null,
      aiExperienceScore: parseDouble(json['ai_experience_score'] ?? json['experience_score']),
      aiSkillsScore: parseDouble(json['ai_skills_score'] ?? json['skills_score']),
      aiProjectsScore: parseDouble(json['ai_projects_score'] ?? json['projects_score']),
      aiRecommendation: json['ai_recommendation'] ?? json['recommendation'],
      aiSummary: json['ai_summary'] ?? json['summary'],
      aiMatchedSkills: parseList(json['ai_matched_skills'] ?? json['matched_skills']),
      aiMissingSkills: parseList(json['ai_missing_skills'] ?? json['missing_skills']),
    );
  }

  /// Create an updated copy of this model (useful for reactive state updates)
  InquiryModel copyWith({
    bool? isPinned,
    String? status,
    String? employerReply,
    String? repliedAt,
    String? interviewScheduledAt,
    String? meetingType,
    String? meetingLinkOrLocation,
    String? interviewNotes,
    double? aiMatchScore,
    int? aiRank,
    String? aiRecommendation,
  }) {
    return InquiryModel(
      id: id,
      jobPostingId: jobPostingId,
      jobTitle: jobTitle,
      candidateName: candidateName,
      candidateEmail: candidateEmail,
      candidatePhone: candidatePhone,
      candidateHeadline: candidateHeadline,
      platform: platform,
      type: type,
      message: message,
      resumePath: resumePath,
      status: status ?? this.status,
      isPinned: isPinned ?? this.isPinned,
      pinnedAt: pinnedAt,
      employerReply: employerReply ?? this.employerReply,
      repliedAt: repliedAt ?? this.repliedAt,
      createdAt: createdAt,
      interviewScheduledAt: interviewScheduledAt ?? this.interviewScheduledAt,
      meetingType: meetingType ?? this.meetingType,
      meetingLinkOrLocation: meetingLinkOrLocation ?? this.meetingLinkOrLocation,
      interviewNotes: interviewNotes ?? this.interviewNotes,
      aiMatchScore: aiMatchScore ?? this.aiMatchScore,
      aiRank: aiRank ?? this.aiRank,
      aiExperienceScore: aiExperienceScore,
      aiSkillsScore: aiSkillsScore,
      aiProjectsScore: aiProjectsScore,
      aiRecommendation: aiRecommendation ?? this.aiRecommendation,
      aiSummary: aiSummary,
      aiMatchedSkills: aiMatchedSkills,
      aiMissingSkills: aiMissingSkills,
    );
  }
}
