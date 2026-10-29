class MockMessageModel {
  final String role; // 'interviewer' or 'candidate'
  final String content;
  final int? timestamp;

  const MockMessageModel({
    required this.role,
    required this.content,
    this.timestamp,
  });

  factory MockMessageModel.fromJson(Map<String, dynamic> json) {
    return MockMessageModel(
      role: json['role'] as String? ?? 'interviewer',
      content: json['content'] as String? ?? '',
      timestamp: json['timestamp'] is int ? json['timestamp'] as int : null,
    );
  }

  Map<String, dynamic> toJson() => {
    'role': role,
    'content': content,
    if (timestamp != null) 'timestamp': timestamp,
  };
}

class MockEvaluationReportModel {
  final int overallScore;
  final String verdict;
  final String summary;
  final List<String> strengths;
  final List<String> areasForImprovement;
  final List<String> recommendations;

  const MockEvaluationReportModel({
    required this.overallScore,
    required this.verdict,
    required this.summary,
    this.strengths = const [],
    this.areasForImprovement = const [],
    this.recommendations = const [],
  });

  factory MockEvaluationReportModel.fromJson(Map<String, dynamic> json) {
    List<String> parseList(dynamic val) {
      if (val is List) {
        return val.map((e) => e.toString()).toList();
      }
      return [];
    }

    int score = 70;
    if (json['overall_score'] is int) {
      score = json['overall_score'] as int;
    } else if (json['overall_score'] != null) {
      score = int.tryParse(json['overall_score'].toString()) ?? 70;
    }

    return MockEvaluationReportModel(
      overallScore: score,
      verdict: json['verdict'] as String? ?? 'Hire',
      summary: json['summary'] as String? ?? '',
      strengths: parseList(json['strengths']),
      areasForImprovement: parseList(json['areas_for_improvement'] ?? json['weaknesses']),
      recommendations: parseList(json['recommendations']),
    );
  }
}

class MockInterviewSessionModel {
  final int id;
  final String roleTitle;
  final String experienceLevel;
  final String techStack;
  final int currentQuestionIndex;
  final int totalQuestions;
  final String status;
  final List<MockMessageModel> conversation;
  final MockEvaluationReportModel? evaluationReport;
  final String? latestQuestion;

  const MockInterviewSessionModel({
    required this.id,
    required this.roleTitle,
    required this.experienceLevel,
    required this.techStack,
    required this.currentQuestionIndex,
    required this.totalQuestions,
    required this.status,
    this.conversation = const [],
    this.evaluationReport,
    this.latestQuestion,
  });

  factory MockInterviewSessionModel.fromJson(Map<String, dynamic> json) {
    List<MockMessageModel> msgs = [];
    if (json['conversation'] is List) {
      msgs = (json['conversation'] as List)
          .map((m) => MockMessageModel.fromJson(Map<String, dynamic>.from(m as Map)))
          .toList();
    }

    MockEvaluationReportModel? report;
    if (json['evaluation_report'] is Map) {
      report = MockEvaluationReportModel.fromJson(Map<String, dynamic>.from(json['evaluation_report'] as Map));
    }

    return MockInterviewSessionModel(
      id: json['id'] is int ? json['id'] as int : int.parse(json['id'].toString()),
      roleTitle: json['role_title'] as String? ?? 'Technical Role',
      experienceLevel: json['experience_level'] as String? ?? 'Mid-Level',
      techStack: json['tech_stack'] as String? ?? '',
      currentQuestionIndex: json['current_question_index'] is int ? json['current_question_index'] as int : 1,
      totalQuestions: json['total_questions'] is int ? json['total_questions'] as int : 5,
      status: json['status'] as String? ?? 'in_progress',
      conversation: msgs,
      evaluationReport: report,
      latestQuestion: json['latest_question'] as String?,
    );
  }
}

