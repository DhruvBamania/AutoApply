import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/constants/app_colors.dart';
import '../../models/job_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/candidate_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';
import '../../widgets/status_badge.dart';
import 'candidate_scouted_screen.dart';

/// JobDiscoveryScreen: Public Job Feed with 1-Tap Apply & Inquiry Modal
class JobDiscoveryScreen extends ConsumerStatefulWidget {
  const JobDiscoveryScreen({super.key});

  @override
  ConsumerState<JobDiscoveryScreen> createState() => _JobDiscoveryScreenState();
}

class _JobDiscoveryScreenState extends ConsumerState<JobDiscoveryScreen> {
  final _searchController = TextEditingController();
  String _selectedJobType = 'all';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(candidateProvider.notifier).fetchPublicJobs();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _triggerSearch() {
    ref.read(candidateProvider.notifier).fetchPublicJobs(
          search: _searchController.text.trim(),
          jobType: _selectedJobType == 'all' ? null : _selectedJobType,
        );
  }

  void _openApplyModal(JobModel job) {
    final user = ref.read(authProvider).user;
    final nameController = TextEditingController(text: user?.name ?? '');
    final emailController = TextEditingController(text: user?.email ?? '');
    final phoneController = TextEditingController();
    final headlineController = TextEditingController();
    final messageController = TextEditingController();
    String applyType = 'application'; // 'application' or 'query'
    PlatformFile? selectedResume;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Text(
                          'Apply to ${job.title}',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        const Spacer(),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    Text(
                      '${job.companyName} • ${job.location}',
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                    ),
                    const SizedBox(height: 14),

                    // Type Selector (Application vs Query)
                    Row(
                      children: [
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Job Application'),
                            selected: applyType == 'application',
                            onSelected: (_) => setModalState(() => applyType = 'application'),
                            selectedColor: AppColors.primary.withValues(alpha: 0.15),
                            labelStyle: TextStyle(
                              fontSize: 12,
                              fontWeight: applyType == 'application' ? FontWeight.bold : FontWeight.normal,
                              color: applyType == 'application' ? AppColors.primary : AppColors.textPrimary,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ChoiceChip(
                            label: const Text('Ask a Question / Query'),
                            selected: applyType == 'query',
                            onSelected: (_) => setModalState(() => applyType = 'query'),
                            selectedColor: AppColors.secondary.withValues(alpha: 0.15),
                            labelStyle: TextStyle(
                              fontSize: 12,
                              fontWeight: applyType == 'query' ? FontWeight.bold : FontWeight.normal,
                              color: applyType == 'query' ? AppColors.secondary : AppColors.textPrimary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    CustomTextField(
                      label: 'Full Name',
                      hint: 'John Doe',
                      controller: nameController,
                    ),
                    const SizedBox(height: 10),

                    CustomTextField(
                      label: 'Email',
                      hint: 'john@example.com',
                      controller: emailController,
                      keyboardType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 10),

                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            label: 'Phone (Optional)',
                            hint: '+91 9876543210',
                            controller: phoneController,
                            keyboardType: TextInputType.phone,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: CustomTextField(
                            label: 'Headline',
                            hint: 'Software Engineer',
                            controller: headlineController,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    CustomTextField(
                      label: applyType == 'application' ? 'Cover Note / Introduction' : 'Your Query / Comment',
                      hint: applyType == 'application'
                          ? 'I am excited to apply for this role because...'
                          : 'Is this position open for remote candidates?',
                      controller: messageController,
                      maxLines: 3,
                    ),
                    const SizedBox(height: 12),

                    // Resume Attachment (PDF/DOCX)
                    const Text('Attach Resume (PDF/DOCX)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 6),
                    if (selectedResume == null)
                      OutlinedButton.icon(
                        onPressed: () async {
                          final res = await FilePicker.platform.pickFiles(
                            type: FileType.custom,
                            allowedExtensions: ['pdf', 'doc', 'docx', 'txt'],
                            withData: kIsWeb,
                          );
                          if (res != null && res.files.isNotEmpty) {
                            setModalState(() => selectedResume = res.files.first);
                          }
                        },
                        icon: const Icon(Icons.attach_file, size: 16),
                        label: const Text('Select Resume File', style: TextStyle(fontSize: 12)),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.description, size: 16, color: AppColors.primary),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                selectedResume!.name,
                                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.close, size: 16, color: AppColors.error),
                              onPressed: () => setModalState(() => selectedResume = null),
                            ),
                          ],
                        ),
                      ),
                    const SizedBox(height: 18),

                    CustomButton(
                      text: applyType == 'application' ? 'Submit Application' : 'Send Query',
                      icon: Icons.send,
                      onPressed: () async {
                        final messenger = ScaffoldMessenger.of(context);
                        if (nameController.text.trim().isEmpty || emailController.text.trim().isEmpty) {
                          messenger.showSnackBar(
                            const SnackBar(content: Text('Name and Email are required!')),
                          );
                          return;
                        }

                        Navigator.pop(ctx);
                        final ok = await ref.read(candidateProvider.notifier).applyToJob(
                              jobId: job.id,
                              name: nameController.text.trim(),
                              email: emailController.text.trim(),
                              phone: phoneController.text.trim().isNotEmpty ? phoneController.text.trim() : null,
                              headline: headlineController.text.trim().isNotEmpty ? headlineController.text.trim() : null,
                              message: messageController.text.trim().isNotEmpty
                                  ? messageController.text.trim()
                                  : 'Interested in this role.',
                              type: applyType,
                              resumeFilePath: selectedResume?.path,
                              resumeFileName: selectedResume?.name,
                              resumeFileBytes: selectedResume?.bytes,
                            );

                        if (!mounted) return;
                        if (ok) {
                          messenger.showSnackBar(
                            SnackBar(
                              content: Text(
                                applyType == 'application'
                                    ? 'Application submitted! Employer will review your profile.'
                                    : 'Your query was delivered to the employer!',
                              ),
                              backgroundColor: AppColors.success,
                            ),
                          );
                        }
                      },
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final candidateState = ref.watch(candidateProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Discover Jobs'),
        elevation: 0,
      ),
      body: Column(
        children: [
          // Search & Filters Header
          Container(
            color: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Column(
              children: [
                // Search Input with submit action
                TextField(
                  controller: _searchController,
                  onSubmitted: (_) => _triggerSearch(),
                  style: const TextStyle(fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Search by title, skill, or company...',
                    prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.textLight),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.arrow_forward, size: 18, color: AppColors.primary),
                      onPressed: _triggerSearch,
                    ),
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                // Job Type Filter Chips
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: [
                      _buildChip('all', 'All Types'),
                      _buildChip('Full-time', 'Full-time'),
                      _buildChip('Remote', 'Remote'),
                      _buildChip('Contract', 'Contract'),
                      _buildChip('Internship', 'Internship'),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Scouted by Employers Banner
          InkWell(
            onTap: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const CandidateScoutedScreen()),
              );
            },
            child: Container(
              margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFE0F2FE), Color(0xFFEDE9FE)],
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                ),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFF7DD3FC)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.gps_fixed, size: 18, color: Color(0xFF0284C7)),
                  SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '🎯 Scouted by Employers',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0C4A6E)),
                        ),
                        Text(
                          'View direct invitations from AI Headhunters',
                          style: TextStyle(fontSize: 10, color: Color(0xFF0369A1)),
                        ),
                      ],
                    ),
                  ),
                  Icon(Icons.arrow_forward_ios, size: 12, color: Color(0xFF0284C7)),
                ],
              ),
            ),
          ),

          // Jobs List
          Expanded(
            child: candidateState.isLoadingJobs
                ? const Center(child: CircularProgressIndicator())
                : candidateState.publicJobs.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.work_off_outlined, size: 48, color: AppColors.textLight),
                            const SizedBox(height: 12),
                            const Text(
                              'No jobs match your search',
                              style: TextStyle(fontWeight: FontWeight.w500, color: AppColors.textSecondary),
                            ),
                            const SizedBox(height: 6),
                            TextButton(
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _selectedJobType = 'all');
                                ref.read(candidateProvider.notifier).fetchPublicJobs();
                              },
                              child: const Text('Reset Filters'),
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => ref.read(candidateProvider.notifier).fetchPublicJobs(),
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: candidateState.publicJobs.length,
                          itemBuilder: (context, index) {
                            final job = candidateState.publicJobs[index];
                            return _buildJobCard(job);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildChip(String type, String label) {
    final isSelected = _selectedJobType == type;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (_) {
          setState(() => _selectedJobType = type);
          _triggerSearch();
        },
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

  Widget _buildJobCard(JobModel job) {
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
            Text(
              job.title,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              '${job.companyName} • ${job.location}',
              style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
            ),
            const SizedBox(height: 10),

            // Chips
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(6)),
                  child: Text(job.jobType, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(6)),
                  child: Text(job.experienceLevel, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
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
            const SizedBox(height: 10),

            // Description preview
            Text(
              job.description,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),

            // Syndicated platforms row
            Row(
              children: [
                const Text('Posted to: ', style: TextStyle(fontSize: 11, color: AppColors.textLight)),
                StatusBadge.platform('linkedin'),
                const SizedBox(width: 4),
                StatusBadge.platform('naukri'),
                const SizedBox(width: 4),
                StatusBadge.platform('indeed'),
                const Spacer(),
                IconButton(
                  tooltip: 'Message on WhatsApp',
                  style: IconButton.styleFrom(
                    backgroundColor: const Color(0xFF25D366),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.all(8),
                    minimumSize: const Size(36, 36),
                  ),
                  icon: const Icon(Icons.chat, size: 17),
                  onPressed: () => _openWhatsAppModal(job),
                ),
                const SizedBox(width: 8),
                ElevatedButton.icon(
                  onPressed: () => _openApplyModal(job),
                  icon: const Icon(Icons.send, size: 14),
                  label: const Text('Apply / Inquire', style: TextStyle(fontSize: 12)),
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _openWhatsAppModal(JobModel job) {
    final user = ref.read(authProvider).user;
    final nameController = TextEditingController(text: user?.name ?? '');
    final phoneController = TextEditingController();
    final emailController = TextEditingController(text: user?.email ?? '');
    final messageController = TextEditingController();
    bool isSending = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: const Color(0xFF25D366).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.chat, color: Color(0xFF25D366), size: 22),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Message on WhatsApp',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                              ),
                              Text(
                                '${job.companyName ?? "Hiring Team"} • ${job.title}',
                                style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(ctx),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Quick suggestion chips
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          ActionChip(
                            label: const Text('💰 Salary Range?', style: TextStyle(fontSize: 11)),
                            onPressed: () {
                              messageController.text = 'Hi, could you share more details about the salary range and perks for this role?';
                            },
                          ),
                          const SizedBox(width: 6),
                          ActionChip(
                            label: const Text('🏡 Remote flexibility?', style: TextStyle(fontSize: 11)),
                            onPressed: () {
                              messageController.text = 'Hello, is this position open to fully remote or hybrid candidates?';
                            },
                          ),
                          const SizedBox(width: 6),
                          ActionChip(
                            label: const Text('⏱️ Interview timeline?', style: TextStyle(fontSize: 11)),
                            onPressed: () {
                              messageController.text = 'Hi team, what is the interview process and timeline for this role?';
                            },
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),

                    CustomTextField(
                      label: 'Your Full Name',
                      hint: 'Alex Johnson',
                      controller: nameController,
                    ),
                    const SizedBox(height: 10),

                    CustomTextField(
                      label: 'Your WhatsApp Phone (with Country Code)',
                      hint: '+91 98765 43210',
                      controller: phoneController,
                      keyboardType: TextInputType.phone,
                    ),
                    const SizedBox(height: 10),

                    CustomTextField(
                      label: 'Email Address (Optional)',
                      hint: 'alex@example.com',
                      controller: emailController,
                      keyboardType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 10),

                    CustomTextField(
                      label: 'Your Message',
                      hint: 'Ask questions about role requirements, tech stack, culture...',
                      controller: messageController,
                      maxLines: 3,
                    ),
                    const SizedBox(height: 16),

                    CustomButton(
                      text: isSending ? 'Sending WhatsApp Message...' : 'Send WhatsApp Message',
                      icon: Icons.send,
                      backgroundColor: const Color(0xFF25D366),
                      isLoading: isSending,
                      onPressed: isSending
                          ? null
                          : () async {
                              final name = nameController.text.trim();
                              final phone = phoneController.text.trim();
                              final msg = messageController.text.trim();

                              if (name.isEmpty || phone.isEmpty || msg.isEmpty) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Name, Phone number, and Message are required!')),
                                );
                                return;
                              }

                              setModalState(() => isSending = true);

                              final ok = await ref.read(candidateProvider.notifier).sendWhatsAppInquiry(
                                    jobId: job.id,
                                    name: name,
                                    phone: phone,
                                    email: emailController.text.trim().isEmpty ? null : emailController.text.trim(),
                                    message: msg,
                                  );

                              setModalState(() => isSending = false);

                              if (context.mounted) {
                                Navigator.pop(ctx);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(ok
                                        ? '✅ WhatsApp message delivered to ${job.companyName ?? "employer"}!'
                                        : 'Failed to deliver WhatsApp message.'),
                                    backgroundColor: ok ? const Color(0xFF15803d) : AppColors.error,
                                  ),
                                );
                              }
                            },
                    ),
                    const SizedBox(height: 10),

                    // Direct App Launcher fallback
                    Center(
                      child: TextButton.icon(
                        icon: const Icon(Icons.open_in_new, size: 14, color: Color(0xFF128C7E)),
                        label: const Text(
                          'Or chat in WhatsApp App directly',
                          style: TextStyle(fontSize: 12, color: Color(0xFF128C7E), fontWeight: FontWeight.w600),
                        ),
                        onPressed: () async {
                          final cleanNumber = (job.whatsappNumber ?? '919876543210').replaceAll(RegExp(r'[^0-9]'), '');
                          final text = Uri.encodeComponent('Hi, I am inquiring about the ${job.title} opening on AI Looping.');
                          final uri = Uri.parse('https://wa.me/$cleanNumber?text=$text');
                          if (await canLaunchUrl(uri)) {
                            await launchUrl(uri, mode: LaunchMode.externalApplication);
                          }
                        },
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }
}
