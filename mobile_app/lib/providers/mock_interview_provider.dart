import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/mock_interview_model.dart';

class MockInterviewState {
  final bool isStarting;
  final bool isSubmitting;
  final MockInterviewSessionModel? session;
  final List<MockMessageModel> messages;
  final int currentQuestionIndex;
  final int totalQuestions;
  final bool isCompleted;
  final MockEvaluationReportModel? finalReport;
  final String? errorMessage;
  final int elapsedSeconds;

  const MockInterviewState({
    this.isStarting = false,
    this.isSubmitting = false,
    this.session,
    this.messages = const [],
    this.currentQuestionIndex = 1,
    this.totalQuestions = 5,
    this.isCompleted = false,
    this.finalReport,
    this.errorMessage,
    this.elapsedSeconds = 0,
  });

  MockInterviewState copyWith({
    bool? isStarting,
    bool? isSubmitting,
    MockInterviewSessionModel? session,
    List<MockMessageModel>? messages,
    int? currentQuestionIndex,
    int? totalQuestions,
    bool? isCompleted,
    MockEvaluationReportModel? finalReport,
    String? errorMessage,
    int? elapsedSeconds,
    bool clearError = false,
  }) {
    return MockInterviewState(
      isStarting: isStarting ?? this.isStarting,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      session: session ?? this.session,
      messages: messages ?? this.messages,
      currentQuestionIndex: currentQuestionIndex ?? this.currentQuestionIndex,
      totalQuestions: totalQuestions ?? this.totalQuestions,
      isCompleted: isCompleted ?? this.isCompleted,
      finalReport: finalReport ?? this.finalReport,
      errorMessage: clearError ? null : (errorMessage ?? this.errorMessage),
      elapsedSeconds: elapsedSeconds ?? this.elapsedSeconds,
    );
  }
}

class MockInterviewNotifier extends StateNotifier<MockInterviewState> {
  MockInterviewNotifier() : super(const MockInterviewState());

  final _api = ApiClient();
  Timer? _timer;

  void startTimer() {
    _timer?.cancel();
    state = state.copyWith(elapsedSeconds: 0);
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      state = state.copyWith(elapsedSeconds: state.elapsedSeconds + 1);
    });
  }

  void stopTimer() {
    _timer?.cancel();
    _timer = null;
  }

  /// Start a new mock interview session
  Future<bool> startSession({
    required String roleTitle,
    required String experienceLevel,
    required String techStack,
    int? prepId,
  }) async {
    state = state.copyWith(
      isStarting: true,
      clearError: true,
      isCompleted: false,
      finalReport: null,
      messages: [],
    );

    try {
      final payload = <String, dynamic>{
        'role_title': roleTitle,
        'experience_level': experienceLevel,
        'tech_stack': techStack,
      };
      if (prepId != null) {
        payload['interview_preparation_id'] = prepId;
      }

      final response = await _api.dio.post(
        ApiEndpoints.mockInterviewStart,
        data: payload,
      );

      final data = response.data;
      if (data['status'] == 'success' && data['session'] != null) {
        final session = MockInterviewSessionModel.fromJson(
          Map<String, dynamic>.from(data['session'] as Map),
        );

        List<MockMessageModel> initialMsgs = List.from(session.conversation);
        if (initialMsgs.isEmpty && session.latestQuestion != null && session.latestQuestion!.isNotEmpty) {
          initialMsgs.add(MockMessageModel(
            role: 'interviewer',
            content: session.latestQuestion!,
          ));
        }

        state = state.copyWith(
          isStarting: false,
          session: session,
          messages: initialMsgs,
          currentQuestionIndex: session.currentQuestionIndex,
          totalQuestions: session.totalQuestions,
          isCompleted: session.status == 'completed',
          finalReport: session.evaluationReport,
        );

        startTimer();
        return true;
      } else {
        state = state.copyWith(
          isStarting: false,
          errorMessage: data['message']?.toString() ?? 'Unable to start mock interview.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isStarting: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  /// Submit candidate answer to current question turn
  Future<bool> submitAnswer(String answer) async {
    if (state.session == null || state.isSubmitting) return false;

    final trimmed = answer.trim();
    if (trimmed.isEmpty) return false;

    // Immediately show candidate's answer in message bubble
    final updatedMsgs = List<MockMessageModel>.from(state.messages)
      ..add(MockMessageModel(role: 'candidate', content: trimmed));

    state = state.copyWith(
      isSubmitting: true,
      clearError: true,
      messages: updatedMsgs,
    );

    try {
      final response = await _api.dio.post(
        ApiEndpoints.mockInterviewAnswer(state.session!.id),
        data: {'answer': trimmed},
      );

      final data = response.data;
      if (data['status'] == 'success') {
        final turn = data['turn'] as Map? ?? {};
        final isFinal = turn['is_final'] == true;

        if (isFinal) {
          stopTimer();
          MockEvaluationReportModel? report;
          if (turn['report'] is Map) {
            report = MockEvaluationReportModel.fromJson(Map<String, dynamic>.from(turn['report'] as Map));
          }

          state = state.copyWith(
            isSubmitting: false,
            isCompleted: true,
            finalReport: report,
          );
        } else {
          final nextQNumber = turn['question_number'] is int ? turn['question_number'] as int : state.currentQuestionIndex + 1;
          final interviewerMsg = turn['interviewer_message']?.toString() ?? '';

          final withNextQ = List<MockMessageModel>.from(state.messages);
          if (interviewerMsg.isNotEmpty) {
            withNextQ.add(MockMessageModel(role: 'interviewer', content: interviewerMsg));
          }

          state = state.copyWith(
            isSubmitting: false,
            currentQuestionIndex: nextQNumber,
            messages: withNextQ,
          );
        }
        return true;
      } else {
        state = state.copyWith(
          isSubmitting: false,
          errorMessage: data['message']?.toString() ?? 'Failed to submit answer.',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isSubmitting: false,
        errorMessage: ApiClient.formatError(e),
      );
      return false;
    }
  }

  void reset() {
    stopTimer();
    state = const MockInterviewState();
  }

  @override
  void dispose() {
    stopTimer();
    super.dispose();
  }
}

final mockInterviewProvider =
    StateNotifierProvider<MockInterviewNotifier, MockInterviewState>((ref) {
  return MockInterviewNotifier();
});
