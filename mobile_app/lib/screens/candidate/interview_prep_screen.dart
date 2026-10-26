import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/interview_prep_model.dart';
import '../../providers/interview_prep_provider.dart';
import '../../widgets/custom_button.dart';

class InterviewPrepScreen extends ConsumerStatefulWidget {
  const InterviewPrepScreen({super.key});

  @override
  ConsumerState<InterviewPrepScreen> createState() => _InterviewPrepScreenState();
}

class _InterviewPrepScreenState extends ConsumerState<InterviewPrepScreen> {
  final _roleController = TextEditingController();
  final _techController = TextEditingController();
  String _selectedExperience = 'Mid-Level Engineer (3-5 yrs)';
  bool _useResume = true;
  bool _isGeneratorExpanded = false;
  String _selectedTopic = 'all'; // 'all', 'bookmarked', or topicName
  final Set<String> _expandedQuestions = {};

  final List<String> _experienceOptions = [
    'Fresher / Entry Level (0-1 yrs)',
    'Junior Engineer (1-3 yrs)',
    'Mid-Level Engineer (3-5 yrs)',
    'Senior Software Engineer (5-8 yrs)',
    'Lead / Principal Architect (8+ yrs)',
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(interviewPrepProvider.notifier).fetchPreps();
      ref.read(interviewPrepProvider.notifier).fetchResumeProfile().then((profile) {
        if (profile != null && mounted) {
          if (_roleController.text.isEmpty && profile['role_title'] != null) {
            _roleController.text = profile['role_title'];
          }
          if (_techController.text.isEmpty && profile['tech_stack'] != null) {
            _techController.text = profile['tech_stack'];
          }
        }
      });
    });
  }

  @override
  void dispose() {
    _roleController.dispose();
    _techController.dispose();
    super.dispose();
  }

  void _autofillFromResume() async {
    final profile = await ref.read(interviewPrepProvider.notifier).fetchResumeProfile();
    if (profile != null && mounted) {
      setState(() {
        if (profile['role_title'] != null && profile['role_title'].toString().isNotEmpty) {
          _roleController.text = profile['role_title'];
        }
        if (profile['tech_stack'] != null && profile['tech_stack'].toString().isNotEmpty) {
          _techController.text = profile['tech_stack'];
        }
        if (profile['experience_level'] != null && _experienceOptions.contains(profile['experience_level'])) {
          _selectedExperience = profile['experience_level'];
        }
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Auto-filled role & skills from your resume!'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('No parsed resume data found. Please enter manually.'),
          ),
        );
      }
    }
  }

  void _generateQuestions() async {
    final role = _roleController.text.trim();
    final tech = _techController.text.trim();

    if (role.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your target role.')),
      );
      return;
    }
    if (tech.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter your tech stack / topics.')),
      );
      return;
    }

    FocusScope.of(context).unfocus();
    final ok = await ref.read(interviewPrepProvider.notifier).generatePrep(
          roleTitle: role,
          experienceLevel: _selectedExperience,
          techStack: tech,
          useResume: _useResume,
        );

    if (ok && mounted) {
      setState(() {
        _isGeneratorExpanded = false;
        _selectedTopic = 'all';
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('AI Interview Questions generated successfully!'),
          backgroundColor: AppColors.success,
        ),
      );
    }
  }

  void _openSavedGuidesSheet() {
    final state = ref.read(interviewPrepProvider);
    final notifier = ref.read(interviewPrepProvider.notifier);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
          constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.7),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                children: [
                  const Text(
                    'Saved Interview Guides',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                  const Spacer(),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              if (state.preps.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 30),
                  child: Center(
                    child: Text('No saved interview guides yet.'),
                  ),
                )
              else
                Expanded(
                  child: ListView.builder(
                    itemCount: state.preps.length,
                    itemBuilder: (context, index) {
                      final p = state.preps[index];
                      final isSelected = state.activePrep?.id == p.id;
                      return Card(
                        elevation: 0,
                        margin: const EdgeInsets.only(bottom: 8),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                          side: BorderSide(
                            color: isSelected ? AppColors.primary : Colors.grey.shade200,
                            width: isSelected ? 1.5 : 1.0,
                          ),
                        ),
                        color: isSelected ? const Color(0xFFEEF2FF) : Colors.white,
                        child: ListTile(
                          title: Text(
                            p.roleTitle,
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          subtitle: Text(
                            '${p.totalQuestions} Questions • ${p.experienceLevel}',
                            style: const TextStyle(fontSize: 12),
                          ),
                          onTap: () {
                            notifier.selectPrep(p);
                            setState(() => _selectedTopic = 'all');
                            Navigator.pop(ctx);
                          },
                          trailing: IconButton(
                            icon: const Icon(Icons.delete_outline, size: 20, color: Colors.redAccent),
                            onPressed: () async {
                              await notifier.deletePrep(p.id);
                              if (ctx.mounted) Navigator.pop(ctx);
                            },
                          ),
                        ),
                      );
                    },
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(interviewPrepProvider);
    final activePrep = state.activePrep;

    // Filter questions by selected topic
    List<InterviewQuestionModel> displayedQuestions = [];
    if (activePrep != null) {
      for (final topic in activePrep.topics) {
        for (final q in topic.questions) {
          if (_selectedTopic == 'all') {
            displayedQuestions.add(q);
          } else if (_selectedTopic == 'bookmarked') {
            if (activePrep.bookmarkedIds.contains(q.id)) {
              displayedQuestions.add(q);
            }
          } else if (topic.topicName == _selectedTopic) {
            displayedQuestions.add(q);
          }
        }
      }
    }

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('AI Interview Prep'),
        actions: [
          IconButton(
            tooltip: 'Saved Guides',
            icon: Badge(
              label: Text('${state.preps.length}'),
              isLabelVisible: state.preps.isNotEmpty,
              child: const Icon(Icons.folder_outlined),
            ),
            onPressed: _openSavedGuidesSheet,
          ),
          IconButton(
            tooltip: _isGeneratorExpanded ? 'Hide Generator' : 'New Question Set',
            icon: Icon(_isGeneratorExpanded ? Icons.close : Icons.tune),
            onPressed: () => setState(() => _isGeneratorExpanded = !_isGeneratorExpanded),
          ),
        ],
      ),
      body: state.isLoading && activePrep == null
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () => ref.read(interviewPrepProvider.notifier).fetchPreps(),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Top Hero Banner
                    _buildHeroBanner(),

                    const SizedBox(height: 14),

                    // Collapsible Generator Card
                    if (_isGeneratorExpanded || activePrep == null) ...[
                      _buildGeneratorCard(state.isGenerating),
                      const SizedBox(height: 16),
                    ],

                    if (activePrep != null) ...[
                      // Active Prep Header & Overview
                      _buildActivePrepHeader(activePrep),

                      const SizedBox(height: 12),

                      // Horizontal Topic Filter Chips
                      _buildTopicFilterBar(activePrep),

                      const SizedBox(height: 14),

                      // Questions Count & Global Controls
                      Row(
                        children: [
                          Text(
                            '${displayedQuestions.length} Questions',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                          ),
                          const Spacer(),
                          TextButton.icon(
                            onPressed: () {
                              setState(() {
                                if (_expandedQuestions.length >= displayedQuestions.length) {
                                  _expandedQuestions.clear();
                                } else {
                                  _expandedQuestions.addAll(displayedQuestions.map((q) => q.id));
                                }
                              });
                            },
                            icon: Icon(
                              _expandedQuestions.length >= displayedQuestions.length
                                  ? Icons.unfold_less
                                  : Icons.unfold_more,
                              size: 16,
                            ),
                            label: Text(
                              _expandedQuestions.length >= displayedQuestions.length
                                  ? 'Collapse All'
                                  : 'Expand All',
                              style: const TextStyle(fontSize: 12),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 6),

                      // Questions List
                      if (displayedQuestions.isEmpty)
                        Container(
                          padding: const EdgeInsets.symmetric(vertical: 40),
                          alignment: Alignment.center,
                          child: Column(
                            children: [
                              const Icon(Icons.bookmark_border, size: 48, color: AppColors.textLight),
                              const SizedBox(height: 10),
                              Text(
                                _selectedTopic == 'bookmarked'
                                    ? 'No questions bookmarked yet. Tap the star icon on any question to save it.'
                                    : 'No questions found for this topic.',
                                textAlign: TextAlign.center,
                                style: const TextStyle(color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        )
                      else
                        ...displayedQuestions.asMap().entries.map((entry) {
                          final idx = entry.key + 1;
                          final q = entry.value;
                          return _buildQuestionCard(q, idx, activePrep);
                        }),
                    ] else ...[
                      // Empty state when no prep exists
                      Container(
                        padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
                        alignment: Alignment.center,
                        child: Column(
                          children: [
                            const Icon(Icons.psychology_outlined, size: 64, color: AppColors.primary),
                            const SizedBox(height: 16),
                            const Text(
                              'Ready for Your Next Interview?',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                            ),
                            const SizedBox(height: 8),
                            const Text(
                              'Fill in your target job role above or tap "Auto-fill from Resume" to generate high-yield technical questions dynamically.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: AppColors.textSecondary, fontSize: 13, height: 1.4),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildHeroBanner() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF4F46E5), Color(0xFF7C3AED)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4F46E5).withValues(alpha: 0.25),
            blurRadius: 12,
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
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.auto_awesome, color: Colors.amberAccent, size: 20),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'AI Technical Interview Studio',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    Text(
                      'Dynamic role & seniority tailored questions & model answers',
                      style: TextStyle(color: Colors.white70, fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildGeneratorCard(bool isGenerating) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                const Icon(Icons.tune, size: 18, color: AppColors.primary),
                const SizedBox(width: 6),
                const Text(
                  'Interview Question Generator',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                ),
                const Spacer(),
                ActionChip(
                  avatar: const Icon(Icons.flash_on, size: 14, color: Colors.purple),
                  label: const Text('Auto-fill Resume', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                  backgroundColor: Colors.purple.shade50,
                  side: BorderSide(color: Colors.purple.shade200),
                  onPressed: _autofillFromResume,
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Role Title Input
            TextField(
              controller: _roleController,
              decoration: InputDecoration(
                labelText: 'Target Job Role *',
                hintText: 'e.g. Flutter Mobile Engineer',
                prefixIcon: const Icon(Icons.work_outline, size: 18),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
            const SizedBox(height: 8),

            // Quick Role Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildQuickRoleChip('Flutter Developer'),
                  _buildQuickRoleChip('Full Stack Laravel'),
                  _buildQuickRoleChip('Backend Node.js'),
                  _buildQuickRoleChip('DevOps Engineer'),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // Experience Dropdown
            DropdownButtonFormField<String>(
              initialValue: _selectedExperience,
              decoration: InputDecoration(
                labelText: 'Candidate Experience Level *',
                prefixIcon: const Icon(Icons.trending_up, size: 18),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
              items: _experienceOptions.map((exp) {
                return DropdownMenuItem(value: exp, child: Text(exp, style: const TextStyle(fontSize: 13)));
              }).toList(),
              onChanged: (val) {
                if (val != null) setState(() => _selectedExperience = val);
              },
            ),
            const SizedBox(height: 12),

            // Tech Stack Input
            TextField(
              controller: _techController,
              maxLines: 2,
              decoration: InputDecoration(
                labelText: 'Tech Stack & Topics *',
                hintText: 'e.g. Dart, Flutter, Riverpod, REST API, Git, SQLite',
                prefixIcon: const Icon(Icons.code, size: 18),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
            const SizedBox(height: 6),

            // Quick Tech Chips
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildQuickTechChip('Flutter'),
                  _buildQuickTechChip('Dart'),
                  _buildQuickTechChip('Laravel'),
                  _buildQuickTechChip('PHP'),
                  _buildQuickTechChip('MySQL'),
                  _buildQuickTechChip('System Design'),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Tailor to Resume checkbox
            Row(
              children: [
                Checkbox(
                  value: _useResume,
                  activeColor: AppColors.primary,
                  onChanged: (val) => setState(() => _useResume = val ?? true),
                ),
                const Expanded(
                  child: Text(
                    'Tailor questions to my uploaded resume projects',
                    style: TextStyle(fontSize: 12),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            CustomButton(
              text: isGenerating ? 'AI Generating Questions...' : 'Generate Questions with AI',
              icon: Icons.auto_awesome,
              isLoading: isGenerating,
              backgroundColor: AppColors.primary,
              onPressed: isGenerating ? null : _generateQuestions,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildQuickRoleChip(String role) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ActionChip(
        label: Text(role, style: const TextStyle(fontSize: 11)),
        backgroundColor: Colors.grey.shade100,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        onPressed: () => _roleController.text = role,
      ),
    );
  }

  Widget _buildQuickTechChip(String tech) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ActionChip(
        label: Text('+$tech', style: const TextStyle(fontSize: 11)),
        backgroundColor: const Color(0xFFEEF2FF),
        labelStyle: const TextStyle(color: Color(0xFF4338CA), fontWeight: FontWeight.bold),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        onPressed: () {
          final current = _techController.text.trim();
          if (current.isEmpty) {
            _techController.text = tech;
          } else if (!current.contains(tech)) {
            _techController.text = '$current, $tech';
          }
        },
      ),
    );
  }

  Widget _buildActivePrepHeader(InterviewPrepModel prep) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  prep.experienceLevel,
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFFB45309)),
                ),
              ),
              const Spacer(),
              Text(
                '${prep.totalQuestions} Questions',
                style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            prep.roleTitle,
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
          const SizedBox(height: 4),
          Text(
            'Tech Stack: ${prep.techStack}',
            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
          ),
        ],
      ),
    );
  }

  Widget _buildTopicFilterBar(InterviewPrepModel prep) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: [
          _buildFilterChip('all', 'All (${prep.totalQuestions})'),
          _buildFilterChip('bookmarked', '⭐ Bookmarked (${prep.bookmarkedIds.length})'),
          ...prep.topics.map((t) => _buildFilterChip(t.topicName, '${t.topicName} (${t.questions.length})')),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final isSelected = _selectedTopic == key;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        onSelected: (_) => setState(() => _selectedTopic = key),
        selectedColor: AppColors.primary.withValues(alpha: 0.15),
        labelStyle: TextStyle(
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          color: isSelected ? AppColors.primary : AppColors.textPrimary,
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      ),
    );
  }

  Widget _buildQuestionCard(InterviewQuestionModel q, int index, InterviewPrepModel prep) {
    final isExpanded = _expandedQuestions.contains(q.id);
    final isBookmarked = prep.bookmarkedIds.contains(q.id);

    Color diffBg = const Color(0xFFFEF3C7);
    Color diffColor = const Color(0xFFB45309);
    if (q.difficulty.toLowerCase() == 'easy') {
      diffBg = const Color(0xFFDCFCE7);
      diffColor = const Color(0xFF15803D);
    } else if (q.difficulty.toLowerCase() == 'hard') {
      diffBg = const Color(0xFFFEE2E2);
      diffColor = const Color(0xFFB91C1C);
    }

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(color: Colors.grey.shade200),
      ),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () {
          setState(() {
            if (isExpanded) {
              _expandedQuestions.remove(q.id);
            } else {
              _expandedQuestions.add(q.id);
            }
          });
        },
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Row: Topic Badge, Difficulty Tag, Bookmark, Chevron
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEEF2FF),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      q.topic,
                      style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF4338CA)),
                    ),
                  ),
                  const SizedBox(width: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: diffBg,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      q.difficulty,
                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: diffColor),
                    ),
                  ),
                  const Spacer(),
                  // Bookmark Star Button
                  InkWell(
                    onTap: () => ref.read(interviewPrepProvider.notifier).toggleBookmark(prep.id, q.id),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 6),
                      child: Icon(
                        isBookmarked ? Icons.star : Icons.star_border,
                        size: 20,
                        color: isBookmarked ? Colors.amber : Colors.grey.shade400,
                      ),
                    ),
                  ),
                  Icon(
                    isExpanded ? Icons.expand_less : Icons.expand_more,
                    size: 20,
                    color: Colors.grey.shade600,
                  ),
                ],
              ),
              const SizedBox(height: 8),

              // Question Title
              Text(
                '#$index. ${q.question}',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, height: 1.35),
              ),

              // Expandable Model Answer & Key Points
              if (isExpanded) ...[
                const SizedBox(height: 12),
                const Divider(height: 1),
                const SizedBox(height: 12),

                // Model Answer
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade50,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.lightbulb_outline, size: 16, color: AppColors.primary),
                          SizedBox(width: 6),
                          Text(
                            'MODEL ANSWER',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: AppColors.primary),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        q.answer,
                        style: const TextStyle(fontSize: 13, height: 1.5, color: Color(0xFF334155)),
                      ),
                    ],
                  ),
                ),

                // Key Talking Points
                if (q.keyPoints.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.check_circle_outline, size: 16, color: Color(0xFF16A34A)),
                            SizedBox(width: 6),
                            Text(
                              'KEY TALKING POINTS (INTERVIEWER LOOKS FOR)',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF15803D)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        ...q.keyPoints.map((point) => Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('• ', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF16A34A))),
                                  Expanded(
                                    child: Text(
                                      point,
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF166534), height: 1.3),
                                    ),
                                  ),
                                ],
                              ),
                            )),
                      ],
                    ),
                  ),
                ],

                // Code Snippet Box (if available)
                if (q.codeSnippet != null && q.codeSnippet!.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F172A),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Row(
                          children: [
                            const Text(
                              'SAMPLE IMPLEMENTATION',
                              style: TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.bold),
                            ),
                            const Spacer(),
                            InkWell(
                              onTap: () {
                                Clipboard.setData(ClipboardData(text: q.codeSnippet!));
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(content: Text('Code copied to clipboard!')),
                                );
                              },
                              child: const Row(
                                children: [
                                  Icon(Icons.copy, size: 12, color: Colors.white70),
                                  SizedBox(width: 4),
                                  Text('Copy', style: TextStyle(color: Colors.white70, fontSize: 11)),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Text(
                            q.codeSnippet!,
                            style: const TextStyle(
                              fontFamily: 'monospace',
                              fontSize: 11,
                              color: Color(0xFF38BDF8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ],
          ),
        ),
      ),
    );
  }
}
