import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/headhunter_match_model.dart';
import '../../providers/headhunter_provider.dart';
import 'interview_prep_screen.dart';

/// CandidateScoutedScreen: In-App Scouted Invitations Hub for Candidates
class CandidateScoutedScreen extends ConsumerStatefulWidget {
  const CandidateScoutedScreen({super.key});

  @override
  ConsumerState<CandidateScoutedScreen> createState() => _CandidateScoutedScreenState();
}

class _CandidateScoutedScreenState extends ConsumerState<CandidateScoutedScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(headhunterProvider.notifier).fetchCandidateInvitations();
    });
  }

  void _confirmDecline(BuildContext context, HeadhunterMatchModel match) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Decline Opportunity?'),
        content: Text('Are you sure you want to decline this invitation from ${match.companyName ?? "the employer"}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            onPressed: () async {
              Navigator.pop(ctx);
              final ok = await ref.read(headhunterProvider.notifier).respondToInvitation(
                    match.id,
                    'declined',
                  );
              if (context.mounted && ok) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Invitation declined.'),
                    backgroundColor: Colors.grey,
                  ),
                );
              }
            },
            child: const Text('Decline', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(headhunterProvider);
    final notifier = ref.read(headhunterProvider.notifier);

    final totalCount = state.candidateInvitations.length;
    final pendingCount = state.candidateInvitations.where((m) => m.status == 'invited').length;
    final acceptedCount = state.candidateInvitations.where((m) => m.status == 'accepted').length;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Scouted by Employers', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
        actions: [
          IconButton(
            tooltip: 'Refresh Invitations',
            icon: const Icon(Icons.refresh),
            onPressed: () => notifier.fetchCandidateInvitations(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => notifier.fetchCandidateInvitations(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE0F2FE), Color(0xFFEDE9FE)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFF7DD3FC)),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF0284C7).withValues(alpha: 0.08),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0284C7),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.gps_fixed, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            'AI Headhunter Invitations',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0C4A6E),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'Employers scouted your tech profile and sent personalized career invitations. Accept to instantly connect and jump directly into their interview shortlist!',
                      style: TextStyle(fontSize: 12, color: Color(0xFF334155), height: 1.4),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),

              // Metrics Row
              Row(
                children: [
                  _buildMetricTile('Total Scouted', '$totalCount', const Color(0xFF0284C7)),
                  const SizedBox(width: 8),
                  _buildMetricTile('Pending Review', '$pendingCount', const Color(0xFFD97706)),
                  const SizedBox(width: 8),
                  _buildMetricTile('Accepted & Connected', '$acceptedCount', const Color(0xFF16A34A)),
                ],
              ),
              const SizedBox(height: 18),

              // State handling
              if (state.isLoading) ...[
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(40),
                    child: CircularProgressIndicator(),
                  ),
                ),
              ] else if (state.candidateInvitations.isEmpty) ...[
                Center(
                  child: Container(
                    margin: const EdgeInsets.only(top: 20),
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.grey.shade200),
                    ),
                    child: Column(
                      children: [
                        const Icon(Icons.mail_outline, size: 48, color: Color(0xFF0284C7)),
                        const SizedBox(height: 12),
                        const Text(
                          'No Scouted Invitations Yet',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Employers use our AI Headhunter to scout talent matching open positions. Keep your resume updated to get scouted!',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF0284C7),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          icon: const Icon(Icons.psychology, size: 18),
                          label: const Text('Prepare with AI Interview Prep'),
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const InterviewPrepScreen()),
                            );
                          },
                        ),
                      ],
                    ),
                  ),
                ),
              ] else ...[
                Text(
                  'Received Invitations (${state.candidateInvitations.length})',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const SizedBox(height: 12),
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: state.candidateInvitations.length,
                  itemBuilder: (context, index) {
                    final match = state.candidateInvitations[index];
                    return _buildInvitationCard(context, match);
                  },
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMetricTile(String label, String value, Color color) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: Column(
          children: [
            Text(
              value,
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: color),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInvitationCard(BuildContext context, HeadhunterMatchModel match) {
    final scoreColor = match.aiMatchScore >= 80
        ? const Color(0xFF16A34A)
        : (match.aiMatchScore >= 60 ? const Color(0xFF0284C7) : const Color(0xFFD97706));

    final isNew = match.status == 'invited';
    final isAccepted = match.status == 'accepted';

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isNew ? const Color(0xFF38BDF8) : (isAccepted ? const Color(0xFF86EFAC) : AppColors.border),
          width: isNew || isAccepted ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Title + Match Badge
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        match.jobTitle ?? 'Tech Opportunity',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        '🏢 ${match.companyName ?? "Direct Tech Employer"}',
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0284C7),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: scoreColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: scoreColor.withValues(alpha: 0.3)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.gps_fixed, size: 12, color: scoreColor),
                      const SizedBox(width: 4),
                      Text(
                        '${match.aiMatchScore}% Match',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                          color: scoreColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),

            // Metadata Chips
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                if (match.jobLocation != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '📍 ${match.jobLocation}',
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                  ),
                if (match.jobSalary != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFDCFCE7),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '💰 ${match.jobSalary}',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                    ),
                  ),
                if (isNew)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      '🌟 New Invitation',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),

            // Pitch Box
            if (match.personalizedPitch != null) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(10),
                  border: const Border(left: BorderSide(color: Color(0xFF16A34A), width: 4)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.mail, size: 14, color: Color(0xFF16A34A)),
                        SizedBox(width: 6),
                        Text(
                          'RECRUITER OUTREACH NOTE',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF166534),
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      match.personalizedPitch!,
                      style: const TextStyle(fontSize: 12, color: Color(0xFF1E293B), height: 1.45),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],

            // Matched Skills
            if (match.skills.isNotEmpty) ...[
              Wrap(
                spacing: 4,
                runSpacing: 4,
                children: match.skills.take(5).map((skill) {
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE0F2FE),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBAE6FD)),
                    ),
                    child: Text(
                      skill,
                      style: const TextStyle(fontSize: 10, color: Color(0xFF0369A1), fontWeight: FontWeight.w600),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 12),
            ],

            // Action Buttons
            if (isNew) ...[
              const Divider(),
              const SizedBox(height: 6),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppColors.textSecondary,
                        side: BorderSide(color: Colors.grey.shade300),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      onPressed: () => _confirmDecline(context, match),
                      child: const Text('✕ Decline', style: TextStyle(fontSize: 12)),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    flex: 2,
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF16A34A),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        elevation: 1,
                      ),
                      icon: const Icon(Icons.handshake_rounded, size: 16),
                      label: const Text(
                        'Accept & Connect',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
                      onPressed: () async {
                        final ok = await ref.read(headhunterProvider.notifier).respondToInvitation(
                              match.id,
                              'accepted',
                            );
                        if (context.mounted && ok) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text('🎉 Connected with ${match.companyName ?? "employer"}!'),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        }
                      },
                    ),
                  ),
                ],
              ),
            ] else if (isAccepted) ...[
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFF86EFAC)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle, color: Color(0xFF15803D), size: 18),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Text(
                        'Connected! You are on the recruiter\'s shortlist.',
                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                      ),
                    ),
                    TextButton(
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const InterviewPrepScreen()),
                        );
                      },
                      child: const Text('Prep Questions →', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            ] else ...[
              const Text(
                'Decline recorded.',
                style: TextStyle(fontSize: 11, color: AppColors.textLight),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
