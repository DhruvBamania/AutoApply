import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/inquiry_model.dart';
import '../../providers/ai_matching_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/shortlist_interview_sheet.dart';

/// GeminiMatchingScreen: AI Candidate Shortlisting & Ranking Leaderboard
/// 
/// Evaluates candidate resumes against the job role description using Gemini AI
/// and displays a 3-metric score breakdown with 1-tap batch shortlisting.
class GeminiMatchingScreen extends ConsumerStatefulWidget {
  final int jobId;
  final String jobTitle;

  const GeminiMatchingScreen({
    super.key,
    required this.jobId,
    required this.jobTitle,
  });

  @override
  ConsumerState<GeminiMatchingScreen> createState() => _GeminiMatchingScreenState();
}

class _GeminiMatchingScreenState extends ConsumerState<GeminiMatchingScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(aiMatchingProvider.notifier).fetchLeaderboard(widget.jobId);
    });
  }

  @override
  Widget build(BuildContext context) {
    final aiState = ref.watch(aiMatchingProvider);
    final notifier = ref.read(aiMatchingProvider.notifier);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('AI Candidate Matcher', style: TextStyle(fontSize: 16)),
            Text(
              widget.jobTitle,
              style: const TextStyle(fontSize: 12, color: Colors.white70),
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Refresh Leaderboard',
            icon: const Icon(Icons.refresh),
            onPressed: () => notifier.fetchLeaderboard(widget.jobId),
          ),
        ],
      ),
      bottomNavigationBar: aiState.selectedForBatch.isNotEmpty
          ? Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.08),
                    blurRadius: 10,
                    offset: const Offset(0, -4),
                  ),
                ],
              ),
              child: SafeArea(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${aiState.selectedForBatch.length} Selected',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const Text(
                            'Ready for batch shortlisting',
                            style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    CustomButton(
                      text: 'Shortlist Selected',
                      icon: Icons.check_circle,
                      isLoading: aiState.isShortlisting,
                      backgroundColor: AppColors.success,
                      onPressed: () async {
                        final messenger = ScaffoldMessenger.of(context);
                        final ok = await notifier.batchShortlist(widget.jobId);
                        if (!mounted) return;
                        if (ok) {
                          messenger.showSnackBar(
                            const SnackBar(
                              content: Text('Candidates shortlisted successfully!'),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),
            )
          : null,
      body: aiState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Gemini Banner & Trigger Card
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.auto_awesome, color: Colors.amberAccent, size: 22),
                            ),
                            const SizedBox(width: 10),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Gemini AI Match & Rank',
                                    style: TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16,
                                    ),
                                  ),
                                  Text(
                                    'Multi-dimensional ATS scoring & skills gap analysis',
                                    style: TextStyle(color: Colors.white70, fontSize: 11),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 14),
                        CustomButton(
                          text: aiState.isMatching ? 'Analyzing Resumes...' : 'Run Gemini AI Matching',
                          isLoading: aiState.isMatching,
                          icon: Icons.flash_on,
                          backgroundColor: Colors.white,
                          onPressed: () async {
                            final messenger = ScaffoldMessenger.of(context);
                            final success = await notifier.runAiMatch(widget.jobId);
                            if (!mounted) return;
                            if (success) {
                              messenger.showSnackBar(
                                const SnackBar(
                                  content: Text('Candidates evaluated and ranked!'),
                                  backgroundColor: AppColors.success,
                                ),
                              );
                            }
                          },
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Quick Filter bar for batch action
                  if (aiState.leaderboard.isNotEmpty) ...[
                    Row(
                      children: [
                        const Text(
                          'Leaderboard',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const Spacer(),
                        TextButton.icon(
                          onPressed: () => notifier.selectTopCandidates(75.0),
                          icon: const Icon(Icons.filter_alt, size: 14),
                          label: const Text('Select >= 75%', style: TextStyle(fontSize: 12)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                  ],

                  // Candidate Cards List
                  if (aiState.leaderboard.isEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 40),
                      alignment: Alignment.center,
                      child: const Column(
                        children: [
                          Icon(Icons.people_outline, size: 48, color: AppColors.textLight),
                          SizedBox(height: 12),
                          Text(
                            'No candidate resumes to evaluate yet',
                            style: TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Applications from LinkedIn, Naukri, and Indeed will show here',
                            style: TextStyle(color: AppColors.textLight, fontSize: 12),
                          ),
                        ],
                      ),
                    )
                  else
                    ...aiState.leaderboard.asMap().entries.map((entry) {
                      final rank = entry.key + 1;
                      final candidate = entry.value;
                      return _buildCandidateCard(candidate, rank, aiState, notifier);
                    }),
                ],
              ),
            ),
    );
  }

  Widget _buildCandidateCard(
    InquiryModel candidate,
    int rank,
    AiMatchingState aiState,
    AiMatchingNotifier notifier,
  ) {
    final isSelected = aiState.selectedForBatch.contains(candidate.id);
    final matchScore = candidate.aiMatchScore ?? 0.0;

    // Rank badge colors (Gold, Silver, Bronze, Slate)
    Color rankBg = Colors.grey.shade200;
    Color rankText = Colors.grey.shade700;
    if (rank == 1) {
      rankBg = const Color(0xFFFEF3C7);
      rankText = const Color(0xFFB45309);
    } else if (rank == 2) {
      rankBg = const Color(0xFFF1F5F9);
      rankText = const Color(0xFF475569);
    } else if (rank == 3) {
      rankBg = const Color(0xFFFFEDD5);
      rankText = const Color(0xFFC2410C);
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 14),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(
          color: isSelected ? AppColors.primary : AppColors.border,
          width: isSelected ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Rank + Name + Selection Checkbox
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: rankBg,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '#$rank',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: rankText),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        candidate.candidateName,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                      Text(
                        candidate.candidateEmail,
                        style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                Checkbox(
                  value: isSelected,
                  activeColor: AppColors.primary,
                  onChanged: (_) => notifier.toggleSelection(candidate.id),
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Badges: Platform, Status, Recommendation
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                StatusBadge.platform(candidate.platform),
                StatusBadge.status(candidate.status),
                if (candidate.aiRecommendation != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.accent.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      candidate.aiRecommendation!,
                      style: const TextStyle(
                        color: AppColors.accent,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),

            // Overall Score Progress Bar
            Row(
              children: [
                const Text(
                  'Overall Match',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
                ),
                const Spacer(),
                Text(
                  '${matchScore.toStringAsFixed(1)}%',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                    color: matchScore >= 75
                        ? AppColors.success
                        : (matchScore >= 50 ? AppColors.warning : AppColors.error),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            ClipRRect(
              borderRadius: BorderRadius.circular(6),
              child: LinearProgressIndicator(
                value: (matchScore / 100).clamp(0.0, 1.0),
                minHeight: 8,
                backgroundColor: Colors.grey.shade200,
                valueColor: AlwaysStoppedAnimation<Color>(
                  matchScore >= 75
                      ? AppColors.success
                      : (matchScore >= 50 ? AppColors.warning : AppColors.error),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // 3-Metric Breakdown
            Row(
              children: [
                _buildSubMetric(
                  label: 'Experience',
                  score: candidate.aiExperienceScore ?? 0.0,
                  icon: Icons.history_edu,
                ),
                _buildSubMetric(
                  label: 'Skills',
                  score: candidate.aiSkillsScore ?? 0.0,
                  icon: Icons.psychology,
                ),
                _buildSubMetric(
                  label: 'Projects',
                  score: candidate.aiProjectsScore ?? 0.0,
                  icon: Icons.folder_special,
                ),
              ],
            ),

            // AI Summary
            if (candidate.aiSummary != null && candidate.aiSummary!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.info_outline, size: 14, color: AppColors.primary),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        candidate.aiSummary!,
                        style: const TextStyle(fontSize: 11, color: AppColors.textPrimary, height: 1.4),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // Matched Skills Chips
            if (candidate.aiMatchedSkills.isNotEmpty) ...[
              const SizedBox(height: 10),
              const Text('Matched Skills:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Wrap(
                spacing: 4,
                runSpacing: 4,
                children: candidate.aiMatchedSkills.map((s) {
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.success.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      s,
                      style: const TextStyle(fontSize: 10, color: AppColors.success, fontWeight: FontWeight.w600),
                    ),
                  );
                }).toList(),
              ),
            ],

            // Missing Skills Chips
            if (candidate.aiMissingSkills.isNotEmpty) ...[
              const SizedBox(height: 8),
              const Text('Missing / Gap Skills:', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Wrap(
                spacing: 4,
                runSpacing: 4,
                children: candidate.aiMissingSkills.map((s) {
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: AppColors.error.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      s,
                      style: const TextStyle(fontSize: 10, color: AppColors.error, fontWeight: FontWeight.w600),
                    ),
                  );
                }).toList(),
              ),
            ],

            // Scheduled Interview Info Badge (if scheduled)
            if (candidate.interviewScheduledAt != null) ...[
              Container(
                margin: const EdgeInsets.only(top: 10),
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEFCE8),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFFDE047)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.event_available, size: 18, color: Color(0xFFB45309)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Interview: ${candidate.interviewScheduledAt!.length >= 16 ? candidate.interviewScheduledAt!.substring(0, 16).replaceAll("T", " ") : candidate.interviewScheduledAt}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF854D0E)),
                          ),
                          Text(
                            'Mode: ${candidate.meetingType == 'google_meet' ? 'Google Meet' : (candidate.meetingType == 'zoom' ? 'Zoom' : (candidate.meetingType == 'in_person' ? 'In-Person' : (candidate.meetingType == 'phone' ? 'Phone' : 'Online')))}',
                            style: const TextStyle(fontSize: 10, color: Color(0xFF713F12)),
                          ),
                        ],
                      ),
                    ),
                    TextButton(
                      onPressed: () => _openShortlistSheet(candidate, notifier),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                      child: const Text('Reschedule', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF854D0E))),
                    ),
                  ],
                ),
              ),
            ],

            // Action Row: "Shortlist & Schedule" button
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                if (candidate.status != 'shortlisted')
                  OutlinedButton.icon(
                    onPressed: () => _openShortlistSheet(candidate, notifier),
                    icon: const Text('⭐', style: TextStyle(fontSize: 12)),
                    label: const Text('Shortlist & Schedule', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF854D0E))),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      backgroundColor: const Color(0xFFFEF08A),
                      side: const BorderSide(color: Color(0xFFFDE047)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  )
                else if (candidate.interviewScheduledAt == null)
                  OutlinedButton.icon(
                    onPressed: () => _openShortlistSheet(candidate, notifier),
                    icon: const Icon(Icons.calendar_month, size: 14, color: AppColors.primary),
                    label: const Text('Schedule Interview', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary)),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      side: const BorderSide(color: AppColors.primary),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _openShortlistSheet(InquiryModel candidate, AiMatchingNotifier notifier) {
    ShortlistInterviewSheet.show(
      context,
      inquiry: candidate,
      jobTitle: widget.jobTitle,
      onConfirm: ({
        required DateTime scheduledAt,
        required String meetingType,
        String? meetingLink,
        String? notes,
        required bool sendEmail,
      }) async {
        final ok = await notifier.shortlistCandidate(
          candidate.id,
          scheduledAt: scheduledAt,
          meetingType: meetingType,
          meetingLink: meetingLink,
          notes: notes,
          sendEmail: sendEmail,
        );
        if (ok && mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(sendEmail
                  ? 'Candidate shortlisted! Interview email sent to ${candidate.candidateEmail}'
                  : 'Candidate shortlisted successfully!'),
              backgroundColor: AppColors.success,
            ),
          );
        }
        return ok;
      },
    );
  }

  Widget _buildSubMetric({
    required String label,
    required double score,
    required IconData icon,
  }) {
    return Expanded(
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 3),
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 6),
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Column(
          children: [
            Icon(icon, size: 14, color: AppColors.textSecondary),
            const SizedBox(height: 4),
            Text(
              '${score.toStringAsFixed(0)}%',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
            ),
            Text(
              label,
              style: const TextStyle(fontSize: 10, color: AppColors.textLight),
            ),
          ],
        ),
      ),
    );
  }
}
