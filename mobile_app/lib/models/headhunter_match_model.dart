class HeadhunterMatchModel {
  final int id;
  final int jobPostingId;
  final int? candidateUserId;
  final String candidateName;
  final String? candidateEmail;
  final String poolSource;
  final String? currentTitle;
  final String? currentCompany;
  final String? location;
  final int experienceYears;
  final List<String> skills;
  final int aiMatchScore;
  final String? aiReasoning;
  final String? personalizedPitch;
  final String status;
  final String? outreachSubject;
  final DateTime? invitedAt;
  final DateTime? respondedAt;
  final String? jobTitle;
  final String? companyName;
  final String? jobLocation;
  final String? jobSalary;

  const HeadhunterMatchModel({
    required this.id,
    required this.jobPostingId,
    this.candidateUserId,
    required this.candidateName,
    this.candidateEmail,
    this.poolSource = 'platform',
    this.currentTitle,
    this.currentCompany,
    this.location,
    this.experienceYears = 0,
    this.skills = const [],
    this.aiMatchScore = 0,
    this.aiReasoning,
    this.personalizedPitch,
    this.status = 'sourced',
    this.outreachSubject,
    this.invitedAt,
    this.respondedAt,
    this.jobTitle,
    this.companyName,
    this.jobLocation,
    this.jobSalary,
  });

  factory HeadhunterMatchModel.fromJson(Map<String, dynamic> json) {
    // Nested job posting if included
    final jobPosting = json['job_posting'] is Map<String, dynamic>
        ? json['job_posting'] as Map<String, dynamic>
        : null;

    final jobCompany = jobPosting != null && jobPosting['user'] is Map<String, dynamic>
        ? jobPosting['user'] as Map<String, dynamic>
        : null;

    // Parse skills list
    List<String> parsedSkills = [];
    if (json['skills'] != null) {
      if (json['skills'] is List) {
        parsedSkills = (json['skills'] as List).map((e) => e.toString()).toList();
      } else if (json['skills'] is String) {
        parsedSkills = (json['skills'] as String)
            .split(',')
            .map((s) => s.trim())
            .where((s) => s.isNotEmpty)
            .toList();
      }
    }

    return HeadhunterMatchModel(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      jobPostingId: json['job_posting_id'] is int
          ? json['job_posting_id'] as int
          : int.tryParse(json['job_posting_id']?.toString() ?? '0') ?? 0,
      candidateUserId: json['candidate_user_id'] != null
          ? int.tryParse(json['candidate_user_id'].toString())
          : null,
      candidateName: json['candidate_name'] as String? ?? 'Candidate',
      candidateEmail: json['candidate_email'] as String?,
      poolSource: json['pool_source'] as String? ?? 'platform',
      currentTitle: json['current_title'] as String?,
      currentCompany: json['current_company'] as String?,
      location: json['location'] as String?,
      experienceYears: json['experience_years'] is int
          ? json['experience_years'] as int
          : int.tryParse(json['experience_years']?.toString() ?? '0') ?? 0,
      skills: parsedSkills,
      aiMatchScore: json['ai_match_score'] is int
          ? json['ai_match_score'] as int
          : int.tryParse(json['ai_match_score']?.toString() ?? '0') ?? 0,
      aiReasoning: json['ai_reasoning'] as String?,
      personalizedPitch: json['personalized_pitch'] as String?,
      status: json['status'] as String? ?? 'sourced',
      outreachSubject: json['outreach_subject'] as String?,
      invitedAt: json['invited_at'] != null ? DateTime.tryParse(json['invited_at'].toString()) : null,
      respondedAt: json['responded_at'] != null ? DateTime.tryParse(json['responded_at'].toString()) : null,
      jobTitle: jobPosting?['title'] as String?,
      companyName: jobCompany?['name'] as String? ?? (jobPosting?['company_name'] as String?),
      jobLocation: jobPosting?['location'] as String?,
      jobSalary: jobPosting?['salary_range'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'job_posting_id': jobPostingId,
      'candidate_user_id': candidateUserId,
      'candidate_name': candidateName,
      'candidate_email': candidateEmail,
      'pool_source': poolSource,
      'current_title': currentTitle,
      'current_company': currentCompany,
      'location': location,
      'experience_years': experienceYears,
      'skills': skills,
      'ai_match_score': aiMatchScore,
      'ai_reasoning': aiReasoning,
      'personalized_pitch': personalizedPitch,
      'status': status,
      'outreach_subject': outreachSubject,
      'invited_at': invitedAt?.toIso8601String(),
      'responded_at': respondedAt?.toIso8601String(),
    };
  }

  HeadhunterMatchModel copyWith({
    int? id,
    int? jobPostingId,
    int? candidateUserId,
    String? candidateName,
    String? candidateEmail,
    String? poolSource,
    String? currentTitle,
    String? currentCompany,
    String? location,
    int? experienceYears,
    List<String>? skills,
    int? aiMatchScore,
    String? aiReasoning,
    String? personalizedPitch,
    String? status,
    String? outreachSubject,
    DateTime? invitedAt,
    DateTime? respondedAt,
    String? jobTitle,
    String? companyName,
    String? jobLocation,
    String? jobSalary,
  }) {
    return HeadhunterMatchModel(
      id: id ?? this.id,
      jobPostingId: jobPostingId ?? this.jobPostingId,
      candidateUserId: candidateUserId ?? this.candidateUserId,
      candidateName: candidateName ?? this.candidateName,
      candidateEmail: candidateEmail ?? this.candidateEmail,
      poolSource: poolSource ?? this.poolSource,
      currentTitle: currentTitle ?? this.currentTitle,
      currentCompany: currentCompany ?? this.currentCompany,
      location: location ?? this.location,
      experienceYears: experienceYears ?? this.experienceYears,
      skills: skills ?? this.skills,
      aiMatchScore: aiMatchScore ?? this.aiMatchScore,
      aiReasoning: aiReasoning ?? this.aiReasoning,
      personalizedPitch: personalizedPitch ?? this.personalizedPitch,
      status: status ?? this.status,
      outreachSubject: outreachSubject ?? this.outreachSubject,
      invitedAt: invitedAt ?? this.invitedAt,
      respondedAt: respondedAt ?? this.respondedAt,
      jobTitle: jobTitle ?? this.jobTitle,
      companyName: companyName ?? this.companyName,
      jobLocation: jobLocation ?? this.jobLocation,
      jobSalary: jobSalary ?? this.jobSalary,
    );
  }
}
