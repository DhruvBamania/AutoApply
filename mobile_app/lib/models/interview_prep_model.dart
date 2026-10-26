class InterviewQuestionModel {
  final String id;
  final String topic;
  final String difficulty;
  final String question;
  final String answer;
  final List<String> keyPoints;
  final String? codeSnippet;
  final bool isBookmarked;

  const InterviewQuestionModel({
    required this.id,
    required this.topic,
    required this.difficulty,
    required this.question,
    required this.answer,
    this.keyPoints = const [],
    this.codeSnippet,
    this.isBookmarked = false,
  });

  factory InterviewQuestionModel.fromJson(Map<String, dynamic> json, {List<String> bookmarkedIds = const []}) {
    final qId = json['id']?.toString() ?? '';
    return InterviewQuestionModel(
      id: qId,
      topic: json['topic'] as String? ?? '',
      difficulty: json['difficulty'] as String? ?? 'Medium',
      question: json['question'] as String? ?? '',
      answer: json['answer'] as String? ?? '',
      keyPoints: json['key_points'] != null
          ? List<String>.from((json['key_points'] as List).map((e) => e.toString()))
          : const [],
      codeSnippet: json['code_snippet'] as String?,
      isBookmarked: bookmarkedIds.contains(qId),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'topic': topic,
      'difficulty': difficulty,
      'question': question,
      'answer': answer,
      'key_points': keyPoints,
      'code_snippet': codeSnippet,
    };
  }

  InterviewQuestionModel copyWith({
    String? id,
    String? topic,
    String? difficulty,
    String? question,
    String? answer,
    List<String>? keyPoints,
    String? codeSnippet,
    bool? isBookmarked,
  }) {
    return InterviewQuestionModel(
      id: id ?? this.id,
      topic: topic ?? this.topic,
      difficulty: difficulty ?? this.difficulty,
      question: question ?? this.question,
      answer: answer ?? this.answer,
      keyPoints: keyPoints ?? this.keyPoints,
      codeSnippet: codeSnippet ?? this.codeSnippet,
      isBookmarked: isBookmarked ?? this.isBookmarked,
    );
  }
}

class InterviewTopicModel {
  final String topicName;
  final String description;
  final List<InterviewQuestionModel> questions;

  const InterviewTopicModel({
    required this.topicName,
    required this.description,
    required this.questions,
  });

  factory InterviewTopicModel.fromJson(Map<String, dynamic> json, {List<String> bookmarkedIds = const []}) {
    final rawQs = json['questions'] as List? ?? [];
    return InterviewTopicModel(
      topicName: json['topic_name'] as String? ?? 'General Topics',
      description: json['description'] as String? ?? '',
      questions: rawQs
          .map((q) => InterviewQuestionModel.fromJson(
                Map<String, dynamic>.from(q),
                bookmarkedIds: bookmarkedIds,
              ))
          .toList(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'topic_name': topicName,
      'description': description,
      'questions': questions.map((q) => q.toJson()).toList(),
    };
  }
}

class InterviewPrepModel {
  final int id;
  final int? userId;
  final String roleTitle;
  final String experienceLevel;
  final String techStack;
  final List<InterviewTopicModel> topics;
  final int totalQuestions;
  final List<String> bookmarkedIds;
  final String? createdAt;

  const InterviewPrepModel({
    required this.id,
    this.userId,
    required this.roleTitle,
    required this.experienceLevel,
    required this.techStack,
    this.topics = const [],
    this.totalQuestions = 0,
    this.bookmarkedIds = const [],
    this.createdAt,
  });

  factory InterviewPrepModel.fromJson(Map<String, dynamic> json) {
    final rawBookmarks = json['bookmarked_ids'] as List? ?? [];
    final bookmarkStrings = rawBookmarks.map((e) => e.toString()).toList();

    List<InterviewTopicModel> parsedTopics = [];
    final rawTopics = json['topics_data'] as List? ?? json['topics'] as List? ?? [];
    parsedTopics = rawTopics
        .map((t) => InterviewTopicModel.fromJson(
              Map<String, dynamic>.from(t),
              bookmarkedIds: bookmarkStrings,
            ))
        .toList();

    return InterviewPrepModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      userId: json['user_id'] is int ? json['user_id'] : int.tryParse(json['user_id']?.toString() ?? ''),
      roleTitle: json['role_title'] as String? ?? 'Technical Role',
      experienceLevel: json['experience_level'] as String? ?? 'Mid-Level (3-5 yrs)',
      techStack: json['tech_stack'] as String? ?? '',
      topics: parsedTopics,
      totalQuestions: json['total_questions'] is int
          ? json['total_questions']
          : int.tryParse(json['total_questions']?.toString() ?? '') ?? 0,
      bookmarkedIds: bookmarkStrings,
      createdAt: json['created_at'] as String?,
    );
  }

  InterviewPrepModel copyWith({
    int? id,
    int? userId,
    String? roleTitle,
    String? experienceLevel,
    String? techStack,
    List<InterviewTopicModel>? topics,
    int? totalQuestions,
    List<String>? bookmarkedIds,
    String? createdAt,
  }) {
    return InterviewPrepModel(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      roleTitle: roleTitle ?? this.roleTitle,
      experienceLevel: experienceLevel ?? this.experienceLevel,
      techStack: techStack ?? this.techStack,
      topics: topics ?? this.topics,
      totalQuestions: totalQuestions ?? this.totalQuestions,
      bookmarkedIds: bookmarkedIds ?? this.bookmarkedIds,
      createdAt: createdAt ?? this.createdAt,
    );
  }
}
