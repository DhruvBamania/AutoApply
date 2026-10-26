class UserModel {
  final int id;
  final String name;
  final String email;
  final String role; // 'company' or 'user'
  final String? companyName;
  final String? companyWebsite;
  final String? resumeText;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.companyName,
    this.companyWebsite,
    this.resumeText,
  });

  bool get isCompany => role == 'company';
  bool get isCandidate => role == 'user';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final comp = json['company_profile'] as Map<String, dynamic>?;

    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'User',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? 'user',
      companyName: comp?['company_name'] as String?,
      companyWebsite: comp?['website'] as String?,
      resumeText: json['resume_text'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'role': role,
    'company_name': companyName,
    'website': companyWebsite,
    'resume_text': resumeText,
  };
}
