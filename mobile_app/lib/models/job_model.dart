class JobModel {
  final int id;
  final String title;
  final String jobType;
  final String? location;
  final String? salaryRange;
  final String? experienceRequired;
  final String? skills;
  final String description;
  final String? imagePath;
  final String status;
  final String? linkedinStatus;
  final String? naukriStatus;
  final String? indeedStatus;
  final int inquiriesCount;
  final int newInquiriesCount;
  final int shortlistedCount;
  final String? lastAiMatchAt;
  final String? createdAt;
  final String? companyName;

  JobModel({
    required this.id,
    required this.title,
    required this.jobType,
    this.location,
    this.salaryRange,
    this.experienceRequired,
    this.skills,
    required this.description,
    this.imagePath,
    required this.status,
    this.linkedinStatus,
    this.naukriStatus,
    this.indeedStatus,
    this.inquiriesCount = 0,
    this.newInquiriesCount = 0,
    this.shortlistedCount = 0,
    this.lastAiMatchAt,
    this.createdAt,
    this.companyName,
  });

  String get experienceLevel => experienceRequired ?? 'All Levels';
  String get displayLocation => location ?? 'Remote';
  String get displaySalary => salaryRange ?? 'Competitive';
  String get displayCompany => companyName ?? 'Hiring Company';

  factory JobModel.fromJson(Map<String, dynamic> json) {
    final user = json['user'] as Map<String, dynamic>?;
    final compProfile = user?['company_profile'] as Map<String, dynamic>?;

    return JobModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title'] as String? ?? 'Untitled Job',
      jobType: json['job_type'] as String? ?? 'Full-time',
      location: json['location'] as String?,
      salaryRange: json['salary_range'] as String?,
      experienceRequired: json['experience_required'] as String? ?? json['experience_level'] as String?,
      skills: json['skills'] as String? ?? json['requirements'] as String?,
      description: json['description'] as String? ?? '',
      imagePath: json['image_path'] as String?,
      status: json['status'] as String? ?? 'active',
      linkedinStatus: json['linkedin_status'] as String?,
      naukriStatus: json['naukri_status'] as String?,
      indeedStatus: json['indeed_status'] as String?,
      inquiriesCount: json['inquiries_count'] as int? ?? (json['inquiries'] as List?)?.length ?? 0,
      newInquiriesCount: json['new_inquiries_count'] as int? ?? 0,
      shortlistedCount: json['shortlisted_count'] as int? ?? 0,
      lastAiMatchAt: json['last_ai_match_at'] as String?,
      createdAt: json['created_at'] as String?,
      companyName: compProfile?['company_name'] as String? ?? user?['name'] as String? ?? json['company_name'] as String?,
    );
  }
}
