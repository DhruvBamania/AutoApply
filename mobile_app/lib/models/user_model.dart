class UserModel {
  final int id;
  final String name;
  final String email;
  final String role; // 'company' or 'user'
  final String? companyName;
  final String? companyWebsite;
  final String? whatsappNumber;
  final String? resumeText;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.companyName,
    this.companyWebsite,
    this.whatsappNumber,
    this.resumeText,
  });

  bool get isCompany => role == 'company';
  bool get isCandidate => role == 'user';

  UserModel copyWith({
    int? id,
    String? name,
    String? email,
    String? role,
    String? companyName,
    String? companyWebsite,
    String? whatsappNumber,
    String? resumeText,
  }) {
    return UserModel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      companyName: companyName ?? this.companyName,
      companyWebsite: companyWebsite ?? this.companyWebsite,
      whatsappNumber: whatsappNumber ?? this.whatsappNumber,
      resumeText: resumeText ?? this.resumeText,
    );
  }

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final comp = json['company_profile'] as Map<String, dynamic>?;

    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'User',
      email: json['email'] as String? ?? '',
      role: json['role'] as String? ?? 'user',
      companyName: comp?['company_name'] as String?,
      companyWebsite: comp?['website'] as String?,
      whatsappNumber: (comp?['whatsapp_number'] ?? json['whatsapp_number']) as String?,
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
    'whatsapp_number': whatsappNumber,
    'resume_text': resumeText,
  };
}

