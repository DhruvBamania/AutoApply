import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/job_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/company_jobs_provider.dart';
import '../../widgets/status_badge.dart';
import 'company_inquiries_screen.dart';
import 'create_job_screen.dart';
import 'gemini_matching_screen.dart';
import 'headhunter_screen.dart';

/// CompanyDashboardScreen: Employer Overview with Job Cards & Direct AI Match Action
class CompanyDashboardScreen extends ConsumerStatefulWidget {
  const CompanyDashboardScreen({super.key});

  @override
  ConsumerState<CompanyDashboardScreen> createState() => _CompanyDashboardScreenState();
}

class _CompanyDashboardScreenState extends ConsumerState<CompanyDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(companyJobsProvider.notifier).fetchJobs();
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = ref.watch(authProvider).user;
    final jobsState = ref.watch(companyJobsProvider);
    final notifier = ref.read(companyJobsProvider.notifier);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Employer Dashboard', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            Text(
              user?.companyName ?? user?.name ?? 'Company',
              style: const TextStyle(fontSize: 12, color: Colors.white70),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Refresh',
            icon: const Icon(Icons.refresh),
            onPressed: () => notifier.fetchJobs(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.primary,
        onPressed: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const CreateJobScreen()),
          );
        },
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Post New Job', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      ),
      body: jobsState.isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () => notifier.fetchJobs(),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Overview Metric Cards
                    Row(
                      children: [
                        _buildMetricCard(
                          title: 'Active Roles',
                          value: '${jobsState.jobs.length}',
                          icon: Icons.work,
                          color: AppColors.primary,
                        ),
                        const SizedBox(width: 12),
                        _buildMetricCard(
                          title: 'Total Platforms',
                          value: '3 Multi-Sync',
                          icon: Icons.hub,
                          color: AppColors.accent,
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // Active Job Postings List Header
                    Row(
                      children: [
                        const Text(
                          'Your Published Jobs',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const Spacer(),
                        Text(
                          '${jobsState.jobs.length} postings',
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    if (jobsState.jobs.isEmpty)
                      Container(
                        padding: const EdgeInsets.all(32),
                        alignment: Alignment.center,
                        child: Column(
                          children: [
                            const Icon(Icons.post_add, size: 48, color: AppColors.textLight),
                            const SizedBox(height: 12),
                            const Text(
                              'No jobs posted yet',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Create your first multi-platform job posting to syndicate across LinkedIn, Naukri, and Indeed',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton.icon(
                              onPressed: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(builder: (_) => const CreateJobScreen()),
                                );
                              },
                              icon: const Icon(Icons.rocket_launch),
                              label: const Text('Publish 1st Job'),
                            ),
                          ],
                        ),
                      )
                    else
                      ...jobsState.jobs.map((job) => _buildJobCard(job, notifier)),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildMetricCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.border),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: color.withValues(alpha: 0.12),
              child: Icon(icon, size: 18, color: color),
            ),
            const SizedBox(height: 12),
            Text(
              value,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildJobCard(JobModel job, CompanyJobsNotifier notifier) {
    return Card(
      margin: const EdgeInsets.only(bottom: 14),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: const BorderSide(color: AppColors.border),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top row: Title + Menu
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        job.title,
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${job.companyName} • ${job.location}',
                        style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                      ),
                    ],
                  ),
                ),
                PopupMenuButton<String>(
                  icon: const Icon(Icons.more_vert, size: 20, color: AppColors.textLight),
                  onSelected: (action) {
                    if (action == 'repost') {
                      notifier.repostJob(job.id);
                    } else if (action == 'delete') {
                      notifier.deleteJob(job.id);
                    }
                  },
                  itemBuilder: (_) => [
                    const PopupMenuItem(value: 'repost', child: Text('Repost to Platforms')),
                    const PopupMenuItem(
                      value: 'delete',
                      child: Text('Delete Job', style: TextStyle(color: AppColors.error)),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 10),

            // Metadata Chips: Job Type, Experience, Salary
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    job.jobType,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    job.experienceLevel,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.success.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    job.displaySalary,
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.success),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Platforms Syndication Badges
            Row(
              children: [
                const Text('Syndicated to: ', style: TextStyle(fontSize: 11, color: AppColors.textLight)),
                StatusBadge.platform('linkedin'),
                const SizedBox(width: 4),
                StatusBadge.platform('naukri'),
                const SizedBox(width: 4),
                StatusBadge.platform('indeed'),
              ],
            ),
            const SizedBox(height: 14),

            // Action Buttons: AI Match & View Inquiries
            Row(
              children: [
                // GEMINI AI MATCH BUTTON
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => GeminiMatchingScreen(
                            jobId: job.id,
                            jobTitle: job.title,
                          ),
                        ),
                      );
                    },
                    icon: const Icon(Icons.auto_awesome, size: 16, color: Colors.amberAccent),
                    label: const Text('AI Match & Rank', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // VIEW INQUIRIES BUTTON
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => CompanyInquiriesScreen(initialJobId: job.id),
                        ),
                      );
                    },
                    icon: const Icon(Icons.forum_outlined, size: 16),
                    label: const Text('View Inquiries', style: TextStyle(fontSize: 12)),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      side: const BorderSide(color: AppColors.primary),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),

            // AI HEADHUNTER AGENT BUTTON
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => HeadhunterScreen(
                        jobId: job.id,
                        jobTitle: job.title,
                      ),
                    ),
                  );
                },
                icon: const Icon(Icons.gps_fixed, size: 15, color: Colors.white),
                label: const Text(
                  '🎯 AI Headhunter Agent: Autonomous Sourcing',
                  style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0284C7),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  elevation: 1,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
