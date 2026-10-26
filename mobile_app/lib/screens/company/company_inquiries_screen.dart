import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/inquiry_model.dart';
import '../../providers/company_inquiries_provider.dart';
import '../../providers/company_jobs_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/status_badge.dart';
import '../../widgets/shortlist_interview_sheet.dart';

/// CompanyInquiriesScreen: Centralized Query Hub & Inquiry Pinning
/// 
/// Consolidates queries and comments across LinkedIn, Naukri, Indeed, and Web.
/// Includes 1-tap query pinning, status updates, and employer replies.
class CompanyInquiriesScreen extends ConsumerStatefulWidget {
  final int? initialJobId;

  const CompanyInquiriesScreen({super.key, this.initialJobId});

  @override
  ConsumerState<CompanyInquiriesScreen> createState() => _CompanyInquiriesScreenState();
}

class _CompanyInquiriesScreenState extends ConsumerState<CompanyInquiriesScreen> {
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(companyJobsProvider.notifier).fetchJobs();
      ref.read(companyInquiriesProvider.notifier).fetchInquiries(jobId: widget.initialJobId);
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _openReplyModal(InquiryModel inquiry) {
    final replyController = TextEditingController(text: inquiry.employerReply);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  const Text(
                    'Reply to Candidate Query',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'Replying to ${inquiry.candidateName} (${inquiry.platform.toUpperCase()})',
                style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  '"${inquiry.message}"',
                  style: const TextStyle(fontSize: 13, fontStyle: FontStyle.italic),
                ),
              ),
              const SizedBox(height: 14),
              CustomTextField(
                label: 'Employer Response',
                hint: 'Write your message to the candidate...',
                controller: replyController,
                maxLines: 4,
              ),
              const SizedBox(height: 16),
              CustomButton(
                text: 'Send Reply',
                icon: Icons.send,
                onPressed: () async {
                  if (replyController.text.trim().isEmpty) return;
                  Navigator.pop(ctx);
                  await ref
                      .read(companyInquiriesProvider.notifier)
                      .replyToInquiry(inquiry.id, replyController.text.trim());
                },
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final inqState = ref.watch(companyInquiriesProvider);
    final jobsState = ref.watch(companyJobsProvider);
    final notifier = ref.read(companyInquiriesProvider.notifier);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Inquiries & Query Pinning'),
        elevation: 0,
        actions: [
          // Live LinkedIn / External Comment Sync button
          IconButton(
            tooltip: 'Sync Live Comments',
            icon: inqState.isSyncing
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                  )
                : const Icon(Icons.sync),
            onPressed: inqState.isSyncing ? null : () => notifier.syncLinkedIn(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter Controls Card
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Column(
              children: [
                // Role-wise Job Dropdown
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<int?>(
                      isExpanded: true,
                      value: inqState.selectedJobId,
                      hint: const Text('All Job Roles (Global Feed)', style: TextStyle(fontSize: 13)),
                      items: [
                        const DropdownMenuItem<int?>(
                          value: null,
                          child: Text('All Job Roles (Global Feed)', style: TextStyle(fontSize: 13)),
                        ),
                        ...jobsState.jobs.map((j) {
                          return DropdownMenuItem<int?>(
                            value: j.id,
                            child: Text(
                              j.title,
                              style: const TextStyle(fontSize: 13),
                              overflow: TextOverflow.ellipsis,
                            ),
                          );
                        }),
                      ],
                      onChanged: (val) => notifier.setFilterJob(val),
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                // Platform Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('all', 'All Platforms', inqState.selectedPlatform, notifier.setPlatformFilter),
                      _buildFilterChip('linkedin', 'LinkedIn', inqState.selectedPlatform, notifier.setPlatformFilter),
                      _buildFilterChip('naukri', 'Naukri', inqState.selectedPlatform, notifier.setPlatformFilter),
                      _buildFilterChip('indeed', 'Indeed', inqState.selectedPlatform, notifier.setPlatformFilter),
                      _buildFilterChip('direct', 'Direct Web', inqState.selectedPlatform, notifier.setPlatformFilter),
                    ],
                  ),
                ),
                const SizedBox(height: 4),

                // Status Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildFilterChip('all', 'All Statuses', inqState.selectedStatus, notifier.setStatusFilter),
                      _buildFilterChip('new', 'New', inqState.selectedStatus, notifier.setStatusFilter),
                      _buildFilterChip('shortlisted', 'Shortlisted', inqState.selectedStatus, notifier.setStatusFilter),
                      _buildFilterChip('reviewed', 'Reviewed', inqState.selectedStatus, notifier.setStatusFilter),
                      _buildFilterChip('rejected', 'Rejected', inqState.selectedStatus, notifier.setStatusFilter),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Inquiries Feed
          Expanded(
            child: inqState.isLoading
                ? const Center(child: CircularProgressIndicator())
                : inqState.inquiries.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.forum_outlined, size: 48, color: AppColors.textLight),
                            const SizedBox(height: 12),
                            const Text(
                              'No inquiries found for this filter',
                              style: TextStyle(fontWeight: FontWeight.w500, color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 6),
                            TextButton.icon(
                              onPressed: () => notifier.syncLinkedIn(),
                              icon: const Icon(Icons.sync, size: 16),
                              label: const Text('Sync LinkedIn Comments'),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => notifier.fetchInquiries(),
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: inqState.sortedInquiries.length,
                          itemBuilder: (context, index) {
                            final inquiry = inqState.sortedInquiries[index];
                            return _buildInquiryCard(inquiry, notifier);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(
    String key,
    String label,
    String activeKey,
    Function(String) onSelect,
  ) {
    final isSelected = activeKey == key;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (_) => onSelect(key),
        selectedColor: AppColors.primary.withValues(alpha: 0.15),
        labelStyle: TextStyle(
          fontSize: 11,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? AppColors.primary : AppColors.textPrimary,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        padding: const EdgeInsets.symmetric(horizontal: 4),
      ),
    );
  }

  Widget _buildInquiryCard(InquiryModel inquiry, CompanyInquiriesNotifier notifier) {
    final isPinned = inquiry.isPinned;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      color: isPinned ? const Color(0xFFFFFBEB) : Colors.white, // Highlight pinned queries in warm amber
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(
          color: isPinned ? const Color(0xFFF59E0B) : AppColors.border,
          width: isPinned ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Row: Badges + PIN ACTION BUTTON
            Row(
              children: [
                if (isPinned) ...[
                  StatusBadge.pinned(),
                  const SizedBox(width: 6),
                ],
                StatusBadge.platform(inquiry.platform),
                const SizedBox(width: 6),
                StatusBadge.status(inquiry.status),
                const Spacer(),

                // Toggle Pin Button with animation
                IconButton(
                  tooltip: isPinned ? 'Unpin Query' : 'Pin Query to Top',
                  icon: Icon(
                    isPinned ? Icons.push_pin : Icons.push_pin_outlined,
                    color: isPinned ? const Color(0xFFD97706) : AppColors.textLight,
                    size: 20,
                  ),
                  onPressed: () => notifier.togglePin(inquiry.id),
                ),

                // Status Update Popup Menu
                PopupMenuButton<String>(
                  icon: const Icon(Icons.more_vert, size: 20, color: AppColors.textLight),
                  onSelected: (newStatus) {
                    if (newStatus == 'shortlisted') {
                      _openShortlistSheet(inquiry, notifier);
                    } else {
                      notifier.updateStatus(inquiry.id, newStatus);
                    }
                  },
                  itemBuilder: (_) => [
                    const PopupMenuItem(value: 'shortlisted', child: Text('⭐ Shortlist & Schedule')),
                    const PopupMenuItem(value: 'reviewed', child: Text('Mark Reviewed')),
                    const PopupMenuItem(value: 'rejected', child: Text('Mark Rejected')),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 8),

            // Job Role Tag (if present)
            if (inquiry.jobTitle != null) ...[
              Text(
                'Role: ${inquiry.jobTitle}',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.primary),
              ),
              const SizedBox(height: 4),
            ],

            // Candidate Name & Contact
            Text(
              inquiry.candidateName,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
            ),
            Text(
              inquiry.candidateEmail,
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
            ),

            // Scheduled Interview Info Badge (if scheduled)
            if (inquiry.interviewScheduledAt != null) ...[
              Container(
                margin: const EdgeInsets.symmetric(vertical: 8),
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
                            'Interview: ${inquiry.interviewScheduledAt!.length >= 16 ? inquiry.interviewScheduledAt!.substring(0, 16).replaceAll("T", " ") : inquiry.interviewScheduledAt}',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF854D0E)),
                          ),
                          Text(
                            'Mode: ${inquiry.meetingType == 'google_meet' ? 'Google Meet' : (inquiry.meetingType == 'zoom' ? 'Zoom' : (inquiry.meetingType == 'in_person' ? 'In-Person' : (inquiry.meetingType == 'phone' ? 'Phone' : 'Online')))}',
                            style: const TextStyle(fontSize: 10, color: Color(0xFF713F12)),
                          ),
                        ],
                      ),
                    ),
                    TextButton(
                      onPressed: () => _openShortlistSheet(inquiry, notifier),
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
            const SizedBox(height: 6),

            // Candidate Message / Inquiry Text
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isPinned ? Colors.white : Colors.grey.shade50,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Text(
                inquiry.message,
                style: const TextStyle(fontSize: 13, color: AppColors.textPrimary, height: 1.4),
              ),
            ),

            // Employer Reply Display (if answered)
            if (inquiry.employerReply != null && inquiry.employerReply!.isNotEmpty) ...[
              const SizedBox(height: 10),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(Icons.reply, size: 16, color: AppColors.primary),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Your Reply:',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: AppColors.primary),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            inquiry.employerReply!,
                            style: const TextStyle(fontSize: 12, color: AppColors.textPrimary),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 12),

            // Action Row: "Shortlist" + "Reply" button + Timestamp
            Row(
              children: [
                Text(
                  inquiry.createdAt.length >= 10 ? inquiry.createdAt.substring(0, 10) : '',
                  style: const TextStyle(fontSize: 11, color: AppColors.textLight),
                ),
                const Spacer(),
                if (inquiry.status != 'shortlisted') ...[
                  OutlinedButton.icon(
                    onPressed: () => _openShortlistSheet(inquiry, notifier),
                    icon: const Text('⭐', style: TextStyle(fontSize: 12)),
                    label: const Text('Shortlist', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF854D0E))),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      backgroundColor: const Color(0xFFFEF08A),
                      side: const BorderSide(color: Color(0xFFFDE047)),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                  const SizedBox(width: 8),
                ],
                OutlinedButton.icon(
                  onPressed: () => _openReplyModal(inquiry),
                  icon: const Icon(Icons.reply, size: 14),
                  label: Text(
                    inquiry.employerReply != null ? 'Edit Reply' : 'Reply',
                    style: const TextStyle(fontSize: 12),
                  ),
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

  void _openShortlistSheet(InquiryModel inquiry, CompanyInquiriesNotifier notifier) {
    ShortlistInterviewSheet.show(
      context,
      inquiry: inquiry,
      jobTitle: inquiry.jobTitle,
      onConfirm: ({
        required DateTime scheduledAt,
        required String meetingType,
        String? meetingLink,
        String? notes,
        required bool sendEmail,
      }) async {
        final ok = await notifier.updateStatus(
          inquiry.id,
          'shortlisted',
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
                  ? 'Candidate shortlisted! Interview email sent to ${inquiry.candidateEmail}'
                  : 'Candidate shortlisted successfully!'),
              backgroundColor: AppColors.success,
            ),
          );
        }
        return ok;
      },
    );
  }
}
