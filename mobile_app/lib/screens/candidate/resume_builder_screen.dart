import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/candidate_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

/// ResumeBuilderScreen: Gemini AI ATS Resume Tailoring & 15 Templates Gallery
class ResumeBuilderScreen extends ConsumerStatefulWidget {
  const ResumeBuilderScreen({super.key});

  @override
  ConsumerState<ResumeBuilderScreen> createState() => _ResumeBuilderScreenState();
}

class _ResumeBuilderScreenState extends ConsumerState<ResumeBuilderScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  final _roleController = TextEditingController(text: 'Senior Full Stack Engineer');
  final _descriptionController = TextEditingController(
    text: 'Looking for a Senior Full Stack Engineer proficient in Flutter, Laravel REST APIs, and Gemini AI integration.',
  );
  int? _selectedResumeId;
  String _selectedTemplateId = '1';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(candidateProvider.notifier).fetchResumes();
      ref.read(candidateProvider.notifier).fetchTemplates();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _roleController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _handleTailor() async {
    final state = ref.read(candidateProvider);
    final resumeId = _selectedResumeId ?? (state.resumes.isNotEmpty ? state.resumes.first.id : 1);

    final ok = await ref.read(candidateProvider.notifier).tailorResume(
          resumeId: resumeId,
          targetJobRole: _roleController.text.trim(),
          targetJobDescription: _descriptionController.text.trim(),
          templateId: _selectedTemplateId,
        );

    if (ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Resume tailored by Gemini AI! Check results below.'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final candidateState = ref.watch(candidateProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('AI ATS Resume Studio'),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
          tabs: const [
            Tab(icon: Icon(Icons.auto_awesome, size: 18), text: 'Gemini ATS Tailor'),
            Tab(icon: Icon(Icons.style, size: 18), text: '15 Pro Templates'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // TAB 1: Gemini AI ATS Tailoring
          SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Info banner
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                  ),
                  child: const Row(
                    children: [
                      Icon(Icons.bolt, color: AppColors.primary, size: 22),
                      SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Gemini AI optimizes your resume keywords & bullet points to guarantee 90%+ ATS screening pass rate.',
                          style: TextStyle(fontSize: 12, color: AppColors.textPrimary, height: 1.4),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Form card
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      CustomTextField(
                        label: 'Target Job Role',
                        hint: 'e.g. Senior Flutter Engineer',
                        controller: _roleController,
                        prefixIcon: Icons.badge_outlined,
                      ),
                      const SizedBox(height: 14),

                      CustomTextField(
                        label: 'Target Job Description (for Keyword Extraction)',
                        hint: 'Paste the requirements and duties of the role you want to apply for...',
                        controller: _descriptionController,
                        maxLines: 4,
                      ),
                      const SizedBox(height: 16),

                      CustomButton(
                        text: candidateState.isTailoring ? 'Analyzing & Tailoring...' : 'Tailor Resume with Gemini AI',
                        isLoading: candidateState.isTailoring,
                        icon: Icons.auto_awesome,
                        onPressed: _handleTailor,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Tailored Results Card (if generated)
                if (candidateState.latestTailored != null) ...[
                  _buildTailoredResultCard(candidateState.latestTailored!),
                  const SizedBox(height: 24),
                ],
              ],
            ),
          ),

          // TAB 2: 15 Professional Templates Gallery
          _buildTemplatesGallery(candidateState),
        ],
      ),
    );
  }

  Widget _buildTailoredResultCard(TailoredResumeModel tailored) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.success, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.check_circle, color: AppColors.success, size: 22),
              const SizedBox(width: 8),
              const Text(
                'ATS Optimized Result',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              const Spacer(),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.success.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${tailored.atsScore.toStringAsFixed(0)}% ATS Match',
                  style: const TextStyle(color: AppColors.success, fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Keyword Badges
          if (tailored.matchedKeywords.isNotEmpty) ...[
            const Text('Keywords Injected for ATS Parsing:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: tailored.matchedKeywords.map((kw) {
                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.primary.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    kw,
                    style: const TextStyle(color: AppColors.primary, fontSize: 11, fontWeight: FontWeight.w600),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 14),
          ],

          // Tailored Content Preview
          const Text('Tailored Resume Content:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Text(
              tailored.tailoredText,
              style: const TextStyle(fontSize: 12, height: 1.5, color: AppColors.textPrimary),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTemplatesGallery(CandidateState state) {
    // 15 templates list (built-in gallery)
    final templateNames = [
      {'id': '1', 'name': 'Silicon Valley Tech', 'cat': 'Tech / Engineering', 'desc': 'High-contrast 2-column layout designed for tech leads'},
      {'id': '2', 'name': 'Executive Corporate', 'cat': 'Management', 'desc': 'Clean serif typography emphasizing career trajectory'},
      {'id': '3', 'name': 'Minimalist Pure', 'cat': 'All Industries', 'desc': 'Whitespace-optimized 1-column ATS standard layout'},
      {'id': '4', 'name': 'Modern Creative', 'cat': 'Design / Product', 'desc': 'Vibrant accent borders with highlighted skill meters'},
      {'id': '5', 'name': 'Harvard Classical', 'cat': 'Academic & Legal', 'desc': 'Traditional Ivy League styling approved for ATS software'},
      {'id': '6', 'name': 'Compact 1-Page', 'cat': 'Entry / Mid', 'desc': 'Fits rich project details into exactly one concise page'},
      {'id': '7', 'name': 'Nordic Clean', 'cat': 'Startups', 'desc': 'Minimalist scandinavian design with high readability'},
      {'id': '8', 'name': 'Data Scientist Pro', 'cat': 'Data & AI', 'desc': 'Includes specialized sections for Kaggle, papers, and models'},
      {'id': '9', 'name': 'Product Manager', 'cat': 'Product / Strategy', 'desc': 'Emphasizes business impact, KPIs, and metrics achieved'},
      {'id': '10', 'name': 'Fintech Analyst', 'cat': 'Finance / Banking', 'desc': 'Strict tabular structure ideal for numerical qualifications'},
      {'id': '11', 'name': 'Full-Stack Developer', 'cat': 'Software Dev', 'desc': 'Prominent GitHub, stack badges, and architecture bullets'},
      {'id': '12', 'name': 'Healthcare Professional', 'cat': 'Medical / Clinical', 'desc': 'Organized for certifications, clinical rotations, and licenses'},
      {'id': '13', 'name': 'Marketing & Growth', 'cat': 'Marketing / Sales', 'desc': 'Highlights campaign ROI, CAC reductions, and user metrics'},
      {'id': '14', 'name': 'Consultant Sleek', 'cat': 'Consulting', 'desc': 'Structured for client engagements, problem-solving, and deliverables'},
      {'id': '15', 'name': 'International EuroPass', 'cat': 'Global / Remote', 'desc': 'Compliant with European and multinational recruitment standards'},
    ];

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: templateNames.length,
      itemBuilder: (context, index) {
        final t = templateNames[index];
        final isSelected = _selectedTemplateId == t['id'];

        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
            side: BorderSide(
              color: isSelected ? AppColors.primary : AppColors.border,
              width: isSelected ? 2.0 : 1.0,
            ),
          ),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isSelected ? AppColors.primary.withValues(alpha: 0.12) : Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(
                    child: Text(
                      '#${t['id']}',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: isSelected ? AppColors.primary : AppColors.textSecondary,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        t['name']!,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      Text(
                        t['cat']!,
                        style: const TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        t['desc']!,
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: Icon(
                    isSelected ? Icons.check_circle : Icons.radio_button_unchecked,
                    color: isSelected ? AppColors.primary : AppColors.textLight,
                  ),
                  onPressed: () {
                    setState(() => _selectedTemplateId = t['id']!);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Selected Template #${t['id']}: ${t['name']}')),
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
