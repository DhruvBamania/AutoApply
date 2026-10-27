import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/headhunter_match_model.dart';
import '../../providers/headhunter_provider.dart';
import '../../widgets/custom_button.dart';

/// HeadhunterScreen: Autonomous AI Headhunter Agent for Employers
class HeadhunterScreen extends ConsumerStatefulWidget {
  final int jobId;
  final String jobTitle;

  const HeadhunterScreen({
    super.key,
    required this.jobId,
    required this.jobTitle,
  });

  @override
  ConsumerState<HeadhunterScreen> createState() => _HeadhunterScreenState();
}

class _HeadhunterScreenState extends ConsumerState<HeadhunterScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(headhunterProvider.notifier).fetchJobMatches(widget.jobId);
    });
  }

  void _showOutreachBottomSheet(BuildContext context, HeadhunterMatchModel match, {String initialChannel = 'email'}) {
    final subjectCtrl = TextEditingController(
      text: match.outreachSubject ?? 'Career Opportunity: ${widget.jobTitle}',
    );
    final messageCtrl = TextEditingController(
      text: match.personalizedPitch ??
          'Hi ${match.candidateName},\n\nOur team was impressed by your tech background and we believe you would be an exceptional fit for our ${widget.jobTitle} position.',
    );
    final phoneCtrl = TextEditingController();
    String activeChannel = initialChannel;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Consumer(
          builder: (context, ref, _) {
            final state = ref.watch(headhunterProvider);
            final isWhatsApp = activeChannel == 'whatsapp';

            return Container(
              padding: EdgeInsets.only(
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
                top: 20,
                left: 20,
                right: 20,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade300,
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: (isWhatsApp ? const Color(0xFF25D366) : const Color(0xFF0284C7)).withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            isWhatsApp ? Icons.chat_bubble_rounded : Icons.gps_fixed,
                            color: isWhatsApp ? const Color(0xFF25D366) : const Color(0xFF0284C7),
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                isWhatsApp ? 'WhatsApp Outreach' : 'Review AI Headhunter Outreach',
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                              Text(
                                'Personalized pitch for ${match.candidateName}',
                                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Channel Selector Tabs
                    Container(
                      padding: const EdgeInsets.all(4),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: GestureDetector(
                              onTap: () => setModalState(() => activeChannel = 'email'),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                decoration: BoxDecoration(
                                  color: !isWhatsApp ? Colors.white : Colors.transparent,
                                  borderRadius: BorderRadius.circular(8),
                                  boxShadow: !isWhatsApp
                                      ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)]
                                      : null,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      Icons.email_outlined,
                                      size: 16,
                                      color: !isWhatsApp ? const Color(0xFF0284C7) : AppColors.textSecondary,
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Email Invitation',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: !isWhatsApp ? FontWeight.bold : FontWeight.normal,
                                        color: !isWhatsApp ? const Color(0xFF0284C7) : AppColors.textSecondary,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          Expanded(
                            child: GestureDetector(
                              onTap: () => setModalState(() => activeChannel = 'whatsapp'),
                              child: Container(
                                padding: const EdgeInsets.symmetric(vertical: 8),
                                decoration: BoxDecoration(
                                  color: isWhatsApp ? Colors.white : Colors.transparent,
                                  borderRadius: BorderRadius.circular(8),
                                  boxShadow: isWhatsApp
                                      ? [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 4)]
                                      : null,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(
                                      Icons.chat_bubble_rounded,
                                      size: 16,
                                      color: isWhatsApp ? const Color(0xFF25D366) : AppColors.textSecondary,
                                    ),
                                    const SizedBox(width: 6),
                                    Text(
                                      'WhatsApp',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: isWhatsApp ? FontWeight.bold : FontWeight.normal,
                                        color: isWhatsApp ? const Color(0xFF16A34A) : AppColors.textSecondary,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Candidate & Score Summary
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  match.candidateName,
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                Text(
                                  match.candidateEmail ?? 'Simulated Developer Contact',
                                  style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFDCFCE7),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: const Color(0xFF86EFAC)),
                            ),
                            child: Text(
                              '${match.aiMatchScore}% Match',
                              style: const TextStyle(
                                color: Color(0xFF15803D),
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Conditional Field: Subject (Email) OR Phone (WhatsApp)
                    if (!isWhatsApp) ...[
                      const Text('Subject', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: subjectCtrl,
                        decoration: InputDecoration(
                          filled: true,
                          fillColor: Colors.grey.shade50,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide(color: Colors.grey.shade300),
                          ),
                        ),
                        style: const TextStyle(fontSize: 13),
                      ),
                      const SizedBox(height: 14),
                    ] else ...[
                      const Text("Candidate WhatsApp Number (E.164)", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: phoneCtrl,
                        keyboardType: TextInputType.phone,
                        decoration: InputDecoration(
                          hintText: '+1 555 123 4567 or +91 9876543210',
                          filled: true,
                          fillColor: Colors.grey.shade50,
                          prefixIcon: const Icon(Icons.phone, size: 18, color: Color(0xFF25D366)),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: BorderSide(color: Colors.grey.shade300),
                          ),
                        ),
                        style: const TextStyle(fontSize: 13),
                      ),
                      const SizedBox(height: 14),
                    ],

                    // Message Textarea
                    const Text('Personalized Pitch', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: messageCtrl,
                      maxLines: 5,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: Colors.grey.shade50,
                        contentPadding: const EdgeInsets.all(12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10),
                          borderSide: BorderSide(color: Colors.grey.shade300),
                        ),
                      ),
                      style: const TextStyle(fontSize: 13, height: 1.4),
                    ),
                    const SizedBox(height: 16),

                    // Info Notice
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFBBF7D0)),
                      ),
                      child: Row(
                        children: [
                          Icon(isWhatsApp ? Icons.chat_bubble_outline_rounded : Icons.bolt,
                              color: const Color(0xFF16A34A), size: 18),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              isWhatsApp
                                  ? 'WhatsApp Service: Candidate receives this pitch on WhatsApp with immediate two-way reply capability.'
                                  : 'Automated Pipeline: When accepted, candidate automatically appears in your Job Shortlist.',
                              style: const TextStyle(fontSize: 11, color: Color(0xFF166534)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Dispatch Button
                    CustomButton(
                      text: isWhatsApp ? 'Send WhatsApp Pitch' : 'Send Invitation Email',
                      icon: isWhatsApp ? Icons.chat_bubble_rounded : Icons.send_rounded,
                      isLoading: state.isInviting,
                      backgroundColor: isWhatsApp ? const Color(0xFF25D366) : const Color(0xFF0284C7),
                      onPressed: () async {
                        if (isWhatsApp) {
                          final phone = phoneCtrl.text.trim();
                          if (phone.isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text('Please enter candidate WhatsApp phone number.'),
                                backgroundColor: AppColors.error,
                              ),
                            );
                            return;
                          }
                          final success = await ref.read(headhunterProvider.notifier).sendWhatsAppOutreach(
                                match.id,
                                candidatePhone: phone,
                                message: messageCtrl.text.trim(),
                              );
                          if (context.mounted && success) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('💬 WhatsApp pitch sent to ${match.candidateName}!'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          }
                        } else {
                          final success = await ref.read(headhunterProvider.notifier).sendInvitation(
                                match.id,
                                subject: subjectCtrl.text.trim(),
                                message: messageCtrl.text.trim(),
                              );
                          if (context.mounted && success) {
                            Navigator.pop(ctx);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('✉️ Invitation sent to ${match.candidateName}!'),
                                backgroundColor: AppColors.success,
                              ),
                            );
                          }
                        }
                      },
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(headhunterProvider);
    final notifier = ref.read(headhunterProvider.notifier);

    // Compute metrics
    final totalScouted = state.companyMatches.length;
    final topMatches = state.companyMatches.where((m) => m.aiMatchScore >= 80).length;
    final invitedCount = state.companyMatches.where((m) => m.status != 'sourced').length;
    final acceptedCount = state.companyMatches.where((m) => m.status == 'accepted').length;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('AI Headhunter Scout', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            Text(
              widget.jobTitle,
              style: const TextStyle(fontSize: 12, color: Colors.white70),
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Refresh Scout List',
            icon: const Icon(Icons.refresh),
            onPressed: () => notifier.fetchJobMatches(widget.jobId),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => notifier.fetchJobMatches(widget.jobId),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Banner Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE0F2FE), Color(0xFFF0F9FF)],
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
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Autonomous Talent Scout',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF0C4A6E),
                                ),
                              ),
                              Text(
                                'Hybrid Pool: Platform Resumes + External Tech Talent',
                                style: TextStyle(fontSize: 11, color: Color(0xFF0369A1)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'AI scans candidate skills and experience depth matching "${widget.jobTitle}" and drafts personalized pitches.',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF334155), height: 1.4),
                    ),
                    const SizedBox(height: 14),

                    // Actions in Banner
                    Row(
                      children: [
                        Expanded(
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0284C7),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 11),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              elevation: 2,
                            ),
                            icon: state.isScouting
                                ? const SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                  )
                                : const Icon(Icons.search, size: 18),
                            label: Text(
                              state.isScouting ? 'Scouting...' : 'Scout with AI',
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                            ),
                            onPressed: state.isScouting
                                ? null
                                : () async {
                                    final ok = await notifier.scoutJob(widget.jobId);
                                    if (context.mounted && ok) {
                                      ScaffoldMessenger.of(context).showSnackBar(
                                        const SnackBar(
                                          content: Text('🎯 New candidate profiles scouted!'),
                                          backgroundColor: AppColors.success,
                                        ),
                                      );
                                    }
                                  },
                          ),
                        ),
                        if (topMatches > 0) ...[
                          const SizedBox(width: 8),
                          Expanded(
                            child: OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF15803D),
                                backgroundColor: const Color(0xFFDCFCE7),
                                side: const BorderSide(color: Color(0xFF86EFAC)),
                                padding: const EdgeInsets.symmetric(vertical: 11),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              icon: state.isInviting
                                  ? const SizedBox(
                                      width: 14,
                                      height: 14,
                                      child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF15803D)),
                                    )
                                  : const Icon(Icons.forward_to_inbox, size: 16),
                              label: const Text(
                                'Batch Invite',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                              ),
                              onPressed: state.isInviting
                                  ? null
                                  : () async {
                                      final ok = await notifier.batchInvite(widget.jobId, minScore: 80);
                                      if (context.mounted && ok) {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(
                                            content: Text('🚀 Batch invitations dispatched!'),
                                            backgroundColor: AppColors.success,
                                          ),
                                        );
                                      }
                                    },
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Metrics Row
              Row(
                children: [
                  _buildMetricTile('Scouted', '$totalScouted', const Color(0xFF0284C7)),
                  const SizedBox(width: 8),
                  _buildMetricTile('High Fit (≥80%)', '$topMatches', const Color(0xFF16A34A)),
                  const SizedBox(width: 8),
                  _buildMetricTile('Invited', '$invitedCount', const Color(0xFFD97706)),
                  const SizedBox(width: 8),
                  _buildMetricTile('Accepted', '$acceptedCount', const Color(0xFF7C3AED)),
                ],
              ),
              const SizedBox(height: 20),

              // Candidate List / Empty State
              if (state.isLoading) ...[
                const Center(
                  child: Padding(
                    padding: EdgeInsets.all(40),
                    child: CircularProgressIndicator(),
                  ),
                ),
              ] else if (state.companyMatches.isEmpty) ...[
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
                        const Icon(Icons.gps_fixed, size: 48, color: Color(0xFF0284C7)),
                        const SizedBox(height: 12),
                        const Text(
                          'No Scouted Candidates Yet',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Tap "Scout with AI" above to search talent and evaluate affinity automatically.',
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
                          icon: const Icon(Icons.search, size: 18),
                          label: const Text('Scout Now'),
                          onPressed: () => notifier.scoutJob(widget.jobId),
                        ),
                      ],
                    ),
                  ),
                ),
              ] else ...[
                Text(
                  'Scouted Candidates (${state.companyMatches.length})',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const SizedBox(height: 12),
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: state.companyMatches.length,
                  itemBuilder: (context, index) {
                    final match = state.companyMatches[index];
                    return _buildCandidateCard(context, match);
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
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: color),
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

  Widget _buildCandidateCard(BuildContext context, HeadhunterMatchModel match) {
    final isPlatform = match.poolSource == 'platform';
    final scoreColor = match.aiMatchScore >= 80
        ? const Color(0xFF16A34A)
        : (match.aiMatchScore >= 60 ? const Color(0xFF0284C7) : const Color(0xFFD97706));

    return Card(
      margin: const EdgeInsets.only(bottom: 14),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(
          color: match.status == 'accepted' ? const Color(0xFF86EFAC) : AppColors.border,
          width: match.status == 'accepted' ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Name + Score Badge
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 20,
                  backgroundColor: const Color(0xFF0284C7).withValues(alpha: 0.12),
                  child: Text(
                    match.candidateName.isNotEmpty ? match.candidateName[0].toUpperCase() : 'C',
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0284C7),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        match.candidateName,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      if (match.currentTitle != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          '${match.currentTitle} ${match.currentCompany != null ? '@ ${match.currentCompany}' : ''}',
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
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
                        '${match.aiMatchScore}%',
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
            const SizedBox(height: 10),

            // Metadata Chips (Location, Source, Experience)
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: isPlatform ? const Color(0xFFEDE9FE) : const Color(0xFFE0F2FE),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    isPlatform ? '👤 Platform Profile' : '🌐 External Tech Pool',
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                      color: isPlatform ? const Color(0xFF6D28D9) : const Color(0xFF0369A1),
                    ),
                  ),
                ),
                if (match.location != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '📍 ${match.location}',
                      style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                    ),
                  ),
                if (match.experienceYears > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      '⏳ ${match.experienceYears}y exp',
                      style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 10),

            // Skills chips
            if (match.skills.isNotEmpty) ...[
              Wrap(
                spacing: 4,
                runSpacing: 4,
                children: match.skills.take(5).map((skill) {
                  return Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: Text(
                      skill,
                      style: const TextStyle(fontSize: 10, color: Color(0xFF166534), fontWeight: FontWeight.w600),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 10),
            ],

            // AI Reasoning snippet
            if (match.aiReasoning != null) ...[
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(8),
                  border: const Border(left: BorderSide(color: Color(0xFF0284C7), width: 3)),
                ),
                child: Text(
                  '💡 Assessment: ${match.aiReasoning}',
                  style: const TextStyle(fontSize: 11, color: Color(0xFF334155), height: 1.3),
                ),
              ),
              const SizedBox(height: 12),
            ],

            // Action / Status Row
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                if (match.status == 'accepted')
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFDCFCE7),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      '🎉 Accepted & Shortlisted',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF15803D)),
                    ),
                  )
                else if (match.status == 'invited')
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF3C7),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      '✉️ Invitation Sent',
                      style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                    ),
                  )
                else if (match.status == 'declined')
                  const Text(
                    '✕ Declined',
                    style: TextStyle(fontSize: 11, color: AppColors.error, fontWeight: FontWeight.bold),
                  )
                else
                  const Text(
                    'Ready for outreach',
                    style: TextStyle(fontSize: 11, color: AppColors.textLight),
                  ),

                // Outreach Actions
                if (match.status == 'sourced' || match.status == 'invited')
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      IconButton(
                        tooltip: 'Send WhatsApp Pitch',
                        style: IconButton.styleFrom(
                          backgroundColor: const Color(0xFF25D366).withValues(alpha: 0.12),
                          padding: const EdgeInsets.all(6),
                          minimumSize: const Size(32, 32),
                        ),
                        icon: const Icon(Icons.chat_bubble_rounded, size: 16, color: Color(0xFF16A34A)),
                        onPressed: () => _showOutreachBottomSheet(context, match, initialChannel: 'whatsapp'),
                      ),
                      const SizedBox(width: 8),
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFF0284C7),
                          side: const BorderSide(color: Color(0xFF0284C7)),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        icon: Icon(
                          match.status == 'invited' ? Icons.refresh : Icons.mail_outline,
                          size: 14,
                        ),
                        label: Text(
                          match.status == 'invited' ? 'Resend' : 'Invite',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                        onPressed: () => _showOutreachBottomSheet(context, match, initialChannel: 'email'),
                      ),
                    ],
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
