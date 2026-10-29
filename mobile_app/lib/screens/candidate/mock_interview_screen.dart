import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/mock_interview_model.dart';
import '../../providers/mock_interview_provider.dart';

class MockInterviewScreen extends ConsumerStatefulWidget {
  final String roleTitle;
  final String experienceLevel;
  final String techStack;
  final int? prepId;

  const MockInterviewScreen({
    super.key,
    required this.roleTitle,
    required this.experienceLevel,
    required this.techStack,
    this.prepId,
  });

  @override
  ConsumerState<MockInterviewScreen> createState() => _MockInterviewScreenState();
}

class _MockInterviewScreenState extends ConsumerState<MockInterviewScreen> {
  final _answerController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(mockInterviewProvider.notifier).startSession(
        roleTitle: widget.roleTitle,
        experienceLevel: widget.experienceLevel,
        techStack: widget.techStack,
        prepId: widget.prepId,
      );
    });
  }

  @override
  void dispose() {
    _answerController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  void _handleSubmitAnswer() async {
    final answer = _answerController.text.trim();
    if (answer.isEmpty) return;

    _answerController.clear();
    _scrollToBottom();

    final success = await ref.read(mockInterviewProvider.notifier).submitAnswer(answer);
    if (success) {
      _scrollToBottom();
    }
  }

  String _formatTimer(int totalSeconds) {
    final m = (totalSeconds ~/ 60).toString().padLeft(2, '0');
    final s = (totalSeconds % 60).toString().padLeft(2, '0');
    return '$m:$s';
  }

  Future<bool> _onWillPop() async {
    final state = ref.read(mockInterviewProvider);
    if (state.isCompleted || state.session == null) {
      ref.read(mockInterviewProvider.notifier).reset();
      return true;
    }

    final leave = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Exit Mock Interview?'),
        content: const Text('Are you sure you want to leave? Your active progress will be ended.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Keep Practicing'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            onPressed: () {
              ref.read(mockInterviewProvider.notifier).reset();
              Navigator.pop(ctx, true);
            },
            child: const Text('Exit', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    return leave ?? false;
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(mockInterviewProvider);
    final progressPct = (state.currentQuestionIndex / state.totalQuestions).clamp(0.0, 1.0);

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final shouldPop = await _onWillPop();
        if (shouldPop && context.mounted) {
          Navigator.of(context).pop();
        }
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF0F172A), // Dark slate theme for immersion
        appBar: AppBar(
          backgroundColor: const Color(0xFF1E1B4B),
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back, color: Colors.white),
            onPressed: () async {
              if (await _onWillPop() && context.mounted) {
                Navigator.of(context).pop();
              }
            },
          ),
          title: Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF6366F1), Color(0xFF8B5CF6)],
                  ),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.smart_toy, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      widget.roleTitle,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Row(
                      children: [
                        CircleAvatar(radius: 3, backgroundColor: Color(0xFF10B981)),
                        SizedBox(width: 4),
                        Text(
                          'AI Hiring Manager • Live',
                          style: TextStyle(color: Colors.white70, fontSize: 11),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white24),
              ),
              child: Row(
                children: [
                  const Icon(Icons.timer_outlined, size: 14, color: Colors.amberAccent),
                  const SizedBox(width: 4),
                  Text(
                    _formatTimer(state.elapsedSeconds),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      fontFamily: 'monospace',
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        body: Column(
          children: [
            // Progress Bar
            Container(
              color: const Color(0xFF1E1B4B),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        state.isCompleted
                            ? 'Interview Completed 🎉'
                            : 'Question ${state.currentQuestionIndex} of ${state.totalQuestions}',
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        '${(progressPct * 100).toInt()}%',
                        style: const TextStyle(
                          color: Color(0xFF818CF8),
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: state.isCompleted ? 1.0 : progressPct,
                      backgroundColor: Colors.white12,
                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF6366F1)),
                      minHeight: 5,
                    ),
                  ),
                ],
              ),
            ),

            // Main Conversation or Final Scorecard
            Expanded(
              child: state.isStarting
                  ? const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          CircularProgressIndicator(color: Color(0xFF6366F1)),
                          SizedBox(height: 16),
                          Text(
                            'AI Hiring Manager is analyzing the role...\nPreparing Question 1',
                            textAlign: TextAlign.center,
                            style: TextStyle(color: Colors.white70, fontSize: 13),
                          ),
                        ],
                      ),
                    )
                  : state.isCompleted && state.finalReport != null
                      ? _buildScorecardView(state.finalReport!)
                      : _buildConversationView(state),
            ),

            // Bottom Input Bar (only if interview is not completed)
            if (!state.isCompleted) _buildInputSection(state),
          ],
        ),
      ),
    );
  }

  Widget _buildConversationView(MockInterviewState state) {
    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      itemCount: state.messages.length + (state.isSubmitting ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == state.messages.length && state.isSubmitting) {
          return _buildTypingIndicator();
        }

        final msg = state.messages[index];
        final isInterviewer = msg.role == 'interviewer';

        return Padding(
          padding: const EdgeInsets.only(bottom: 14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: isInterviewer ? MainAxisAlignment.start : MainAxisAlignment.end,
            children: [
              if (isInterviewer) ...[
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: const Color(0xFF4F46E5),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.psychology, color: Colors.white, size: 18),
                ),
                const SizedBox(width: 8),
              ],
              Flexible(
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isInterviewer ? const Color(0xFF1E293B) : const Color(0xFF4338CA),
                    borderRadius: BorderRadius.only(
                      topLeft: const Radius.circular(14),
                      topRight: const Radius.circular(14),
                      bottomLeft: isInterviewer ? const Radius.circular(2) : const Radius.circular(14),
                      bottomRight: isInterviewer ? const Radius.circular(14) : const Radius.circular(2),
                    ),
                    border: Border.all(
                      color: isInterviewer ? Colors.white12 : const Color(0xFF6366F1),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            isInterviewer ? 'Interviewer' : 'You (Candidate)',
                            style: TextStyle(
                              color: isInterviewer ? const Color(0xFF93C5FD) : const Color(0xFFA5B4FC),
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        msg.content,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                          height: 1.45,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              if (!isInterviewer) ...[
                const SizedBox(width: 8),
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B82F6),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.person, color: Colors.white, size: 18),
                ),
              ],
            ],
          ),
        );
      },
    );
  }

  Widget _buildTypingIndicator() {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.white10),
      ),
      child: const Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          SizedBox(
            width: 14,
            height: 14,
            child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF818CF8)),
          ),
          SizedBox(width: 10),
          Text(
            'AI Interviewer is evaluating your answer...',
            style: TextStyle(color: Colors.white70, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildInputSection(MockInterviewState state) {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 16),
      decoration: const BoxDecoration(
        color: Color(0xFF1E1B4B),
        border: Border(top: BorderSide(color: Colors.white12)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: [
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF0F172A),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: Colors.white24),
                ),
                child: TextField(
                  controller: _answerController,
                  maxLines: 4,
                  minLines: 1,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  decoration: const InputDecoration(
                    hintText: 'Type your technical answer thoroughly...',
                    hintStyle: TextStyle(color: Colors.white38, fontSize: 13),
                    border: InputBorder.none,
                    contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  ),
                  enabled: !state.isSubmitting && !state.isStarting,
                ),
              ),
            ),
            const SizedBox(width: 8),
            Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
                ),
                borderRadius: BorderRadius.circular(14),
              ),
              child: IconButton(
                icon: state.isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.send, color: Colors.white),
                onPressed: (state.isSubmitting || state.isStarting) ? null : _handleSubmitAnswer,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildScorecardView(MockEvaluationReportModel report) {
    Color verdictColor = const Color(0xFF10B981);
    if (report.overallScore < 60) {
      verdictColor = const Color(0xFFEF4444);
    } else if (report.overallScore < 75) {
      verdictColor = const Color(0xFFF59E0B);
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.white12),
            ),
            child: Column(
              children: [
                const Icon(Icons.workspace_premium, size: 48, color: Colors.amberAccent),
                const SizedBox(height: 10),
                const Text(
                  'Interview Performance Scorecard',
                  style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 6),
                Text(
                  widget.roleTitle,
                  style: const TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 16),

                // Score Ring
                Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(color: verdictColor, width: 4),
                    color: verdictColor.withValues(alpha: 0.1),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    '${report.overallScore}%',
                    style: TextStyle(
                      color: verdictColor,
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(height: 12),

                // Verdict Badge
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  decoration: BoxDecoration(
                    color: verdictColor.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: verdictColor),
                  ),
                  child: Text(
                    report.verdict.toUpperCase(),
                    style: TextStyle(
                      color: verdictColor,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 1,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // Executive Summary
          if (report.summary.isNotEmpty) ...[
            _buildReportCard(
              title: 'Executive Summary',
              icon: Icons.article_outlined,
              iconColor: const Color(0xFF60A5FA),
              child: Text(
                report.summary,
                style: const TextStyle(color: Colors.white, fontSize: 13, height: 1.5),
              ),
            ),
            const SizedBox(height: 14),
          ],

          // Key Strengths
          if (report.strengths.isNotEmpty) ...[
            _buildReportCard(
              title: 'Demonstrated Strengths',
              icon: Icons.check_circle_outline,
              iconColor: const Color(0xFF10B981),
              child: Column(
                children: report.strengths.map((s) => _buildBulletPoint(s, const Color(0xFF10B981))).toList(),
              ),
            ),
            const SizedBox(height: 14),
          ],

          // Areas for Improvement
          if (report.areasForImprovement.isNotEmpty) ...[
            _buildReportCard(
              title: 'Areas for Improvement',
              icon: Icons.trending_up,
              iconColor: const Color(0xFFF59E0B),
              child: Column(
                children: report.areasForImprovement.map((w) => _buildBulletPoint(w, const Color(0xFFF59E0B))).toList(),
              ),
            ),
            const SizedBox(height: 14),
          ],

          // Recommendations
          if (report.recommendations.isNotEmpty) ...[
            _buildReportCard(
              title: 'Actionable Next Steps',
              icon: Icons.lightbulb_outline,
              iconColor: const Color(0xFFA855F7),
              child: Column(
                children: report.recommendations.map((r) => _buildBulletPoint(r, const Color(0xFFA855F7))).toList(),
              ),
            ),
            const SizedBox(height: 20),
          ],

          // Action Buttons
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: const BorderSide(color: Colors.white30),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    ref.read(mockInterviewProvider.notifier).startSession(
                      roleTitle: widget.roleTitle,
                      experienceLevel: widget.experienceLevel,
                      techStack: widget.techStack,
                      prepId: widget.prepId,
                    );
                  },
                  child: const Text('Retake Interview'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4F46E5),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: () {
                    ref.read(mockInterviewProvider.notifier).reset();
                    Navigator.of(context).pop();
                  },
                  child: const Text('Done Practice', style: TextStyle(color: Colors.white)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  Widget _buildReportCard({
    required String title,
    required IconData icon,
    required Color iconColor,
    required Widget child,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF1E293B),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: iconColor),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ],
          ),
          const Divider(color: Colors.white12, height: 20),
          child,
        ],
      ),
    );
  }

  Widget _buildBulletPoint(String text, Color dotColor) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            margin: const EdgeInsets.only(top: 6),
            width: 6,
            height: 6,
            decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(color: Colors.white, fontSize: 13, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}

