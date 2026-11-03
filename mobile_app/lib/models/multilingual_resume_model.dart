class MultilingualResumeModel {
  final int id;
  final String languageName;
  final String languageCode;
  final String languageFlag;
  final int templateId;
  final String? targetRole;
  final int skillsCount;
  final int experienceCount;
  final String createdAt;
  final Map<String, dynamic>? resumeData;

  const MultilingualResumeModel({
    required this.id,
    required this.languageName,
    required this.languageCode,
    required this.languageFlag,
    required this.templateId,
    this.targetRole,
    this.skillsCount = 0,
    this.experienceCount = 0,
    required this.createdAt,
    this.resumeData,
  });

  factory MultilingualResumeModel.fromJson(Map<String, dynamic> json) {
    Map<String, dynamic>? data;
    if (json['resume_data'] is Map) {
      data = Map<String, dynamic>.from(json['resume_data'] as Map);
    }

    int skills = 0;
    if (json['skills_count'] is int) {
      skills = json['skills_count'] as int;
    } else if (data != null && data['skills'] is List) {
      skills = (data['skills'] as List).length;
    }

    int exp = 0;
    if (json['experience_count'] is int) {
      exp = json['experience_count'] as int;
    } else if (data != null && data['experience'] is List) {
      exp = (data['experience'] as List).length;
    }

    return MultilingualResumeModel(
      id: json['id'] is int ? json['id'] as int : int.parse(json['id'].toString()),
      languageName: json['language_name'] as String? ?? 'English',
      languageCode: json['language_code'] as String? ?? 'en',
      languageFlag: json['language_flag'] as String? ?? '🌐',
      templateId: json['template_id'] is int ? json['template_id'] as int : 1,
      targetRole: json['target_role'] as String?,
      skillsCount: skills,
      experienceCount: exp,
      createdAt: json['created_at'] as String? ?? '',
      resumeData: data,
    );
  }

  String get summarySnippet {
    if (resumeData != null && resumeData!['summary'] != null) {
      return resumeData!['summary'].toString();
    }
    return '';
  }

  String get jobTitle {
    if (resumeData != null && resumeData!['job_title'] != null) {
      return resumeData!['job_title'].toString();
    }
    return targetRole ?? '';
  }
}

