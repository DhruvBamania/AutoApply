import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../models/multilingual_resume_model.dart';
import '../../providers/multilingual_resume_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

/// MultilingualResumeScreen: AI-powered multilingual resume generator & viewer
class MultilingualResumeScreen extends ConsumerStatefulWidget {
  const MultilingualResumeScreen({super.key});

  @override
  ConsumerState<MultilingualResumeScreen> createState() => _MultilingualResumeScreenState();
}

class _MultilingualResumeScreenState extends ConsumerState<MultilingualResumeScreen> {
  final Set<String> _selectedLanguages = {};
  final TextEditingController _customLangController = TextEditingController();
  final TextEditingController _targetRoleController = TextEditingController();
  int _selectedTemplateId = 1;

  final List<Map<String, String>> _fallbackPresets = const [
    {'name': 'German', 'code': 'de', 'flag': '🇩🇪', 'native': 'Deutsch'},
    {'name': 'Spanish', 'code': 'es', 'flag': '🇪🇸', 'native': 'Español'},
    {'name': 'French', 'code': 'fr', 'flag': '🇫🇷', 'native': 'Français'},
    {'name': 'Japanese', 'code': 'ja', 'flag': '🇯🇵', 'native': '日本語'},
    {'name': 'Mandarin Chinese', 'code': 'zh', 'flag': '🇨🇳', 'native': '中文'},
    {'name': 'Hindi', 'code': 'hi', 'flag': '🇮🇳', 'native': 'हिन्दी'},
    {'name': 'Arabic', 'code': 'ar', 'flag': '🇸🇦', 'native': 'العربية'},
    {'name': 'Portuguese', 'code': 'pt', 'flag': '🇧🇷', 'native': 'Português'},
    {'name': 'Italian', 'code': 'it', 'flag': '🇮🇹', 'native': 'Italiano'},
    {'name': 'Dutch', 'code': 'nl', 'flag': '🇳🇱', 'native': 'Nederlands'},
    {'name': 'Russian', 'code': 'ru', 'flag': '🇷🇺', 'native': 'Русский'},
    {'name': 'Korean', 'code': 'ko', 'flag': '🇰🇷', 'native': '한국어'},
    {'name': 'Turkish', 'code': 'tr', 'flag': '🇹🇷', 'native': 'Türkçe'},
    {'name': 'Polish', 'code': 'pl', 'flag': '🇵🇱', 'native': 'Polski'},
    {'name': 'Swedish', 'code': 'sv', 'flag': '🇸🇪', 'native': 'Svenska'},
    {'name': 'Indonesian', 'code': 'id', 'flag': '🇮🇩', 'native': 'Bahasa Indonesia'},
  ];

  final Map<int, String> _templates = const {
    1: 'Template 1 - Minimal ATS',
    2: 'Template 2 - Executive Modern',
    3: 'Template 3 - Tech Modern',
    4: 'Template 4 - Creative Compact',
    5: 'Template 5 - Timeline Accent',
    6: 'Template 6 - Bold Header',
    7: 'Template 7 - Split Professional',
    8: 'Template 8 - Grid Metric',
    9: 'Template 9 - Academic Classic',
    10: 'Template 10 - Modern Sidebar',
    11: 'Template 11 - Startup Sleek',
    12: 'Template 12 - Corporate Clean',
    13: 'Template 13 - Two Column Focus',
    14: 'Template 14 - Compact Technical',
    15: 'Template 15 - Dynamic Accent',
  };

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(multilingualResumeProvider.notifier).fetchResumes();
    });
  }

  @override
  void dispose() {
    _customLangController.dispose();
    _targetRoleController.dispose();
    super.dispose();
  }

  void _toggleLanguage(String name) {
    setState(() {
      if (_selectedLanguages.contains(name)) {
        _selectedLanguages.remove(name);
      } else {
        _selectedLanguages.add(name);
      }
    });
  }

  void _selectBundle(List<String> names) {
    setState(() {
      _selectedLanguages.addAll(names);
    });
  }

  void _clearLanguages() {
    setState(() {
      _selectedLanguages.clear();
    });
  }

  void _addCustomLanguage() {
    final text = _customLangController.text.trim();
    if (text.isEmpty) return;

    final parts = text.split(RegExp(r'[,;]')).map((s) => s.trim()).where((s) => s.isNotEmpty);
    setState(() {
      for (final p in parts) {
        _selectedLanguages.add(p);
      }
      _customLangController.clear();
    });
  }

  Future<void> _handleTranslate() async {
    if (_selectedLanguages.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select at least one language to translate.'),
          backgroundColor: AppColors.warning,
        ),
      );
      return;
    }

    final success = await ref.read(multilingualResumeProvider.notifier).translateResume(
          languages: _selectedLanguages.toList(),
          templateId: _selectedTemplateId,
          targetRole: _targetRoleController.text.trim().isNotEmpty
              ? _targetRoleController.text.trim()
              : null,
        );

    if (mounted) {
      final state = ref.read(multilingualResumeProvider);
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.successMessage ?? 'Translation completed!'),
            backgroundColor: AppColors.success,
          ),
        );
        setState(() {
          _selectedLanguages.clear();
        });
      } else if (state.errorMessage != null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(state.errorMessage!),
            backgroundColor: AppColors.error,
          ),
        );
      }
    }
  }

  void _showResumePreviewSheet(MultilingualResumeModel resume) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        final data = resume.resumeData ?? {};
        final skills = (data['skills'] as List?)?.map((e) => e.toString()).toList() ?? [];
        final experience = (data['experience'] as List?) ?? [];
        final education = (data['education'] as List?) ?? [];

        return DraggableScrollableSheet(
          initialChildSize: 0.85,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          builder: (_, scrollController) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                children: [
                  // Handle bar
                  Center(
                    child: Container(
                      margin: const EdgeInsets.only(top: 12, bottom: 8),
                      width: 48,
                      height: 5,
                      decoration: BoxDecoration(
                        color: Colors.grey.shade300,
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),

                  // Header
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    child: Row(
                      children: [
                        Text(
                          resume.languageFlag,
                          style: const TextStyle(fontSize: 28),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${resume.languageName} Resume',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              Text(
                                'Template #${resume.templateId} • ${resume.createdAt.length >= 10 ? resume.createdAt.substring(0, 10) : ""}',
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: AppColors.textSecondary,
                                ),
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
                  ),
                  const Divider(height: 1),

                  // Content
                  Expanded(
                    child: ListView(
                      controller: scrollController,
                      padding: const EdgeInsets.all(20),
                      children: [
                        // Target Role / Title
                        if (resume.jobTitle.isNotEmpty) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.08),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.work_outline, size: 18, color: AppColors.primary),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    resume.jobTitle,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                      fontSize: 14,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),
                        ],

                        // Professional Summary
                        if (resume.summarySnippet.isNotEmpty) ...[
                          const Text(
                            'Professional Summary',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.grey.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Text(
                              resume.summarySnippet,
                              style: const TextStyle(
                                fontSize: 13,
                                height: 1.5,
                                color: AppColors.textPrimary,
                              ),
                            ),
                          ),
                          const SizedBox(height: 20),
                        ],

                        // Skills
                        if (skills.isNotEmpty) ...[
                          Row(
                            children: [
                              const Text(
                                'Core Skills',
                                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppColors.secondary.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '${skills.length}',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.secondary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: skills.map((skill) {
                              return Chip(
                                label: Text(
                                  skill,
                                  style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                                ),
                                backgroundColor: Colors.indigo.shade50,
                                side: BorderSide(color: Colors.indigo.shade100),
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
                              );
                            }).toList(),
                          ),
                          const SizedBox(height: 20),
                        ],

                        // Experience
                        if (experience.isNotEmpty) ...[
                          const Text(
                            'Work Experience',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          ...experience.map((exp) {
                            final role = exp['role'] ?? exp['title'] ?? 'Role';
                            final comp = exp['company'] ?? '';
                            final duration = exp['duration'] ?? '';
                            final highlights = exp['highlights'] as List? ?? [];

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          role.toString(),
                                          style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ),
                                      if (duration.toString().isNotEmpty)
                                        Text(
                                          duration.toString(),
                                          style: const TextStyle(
                                            fontSize: 11,
                                            color: AppColors.textSecondary,
                                          ),
                                        ),
                                    ],
                                  ),
                                  if (comp.toString().isNotEmpty) ...[
                                    const SizedBox(height: 3),
                                    Text(
                                      comp.toString(),
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: AppColors.primary,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ],
                                  if (highlights.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    ...highlights.map((h) {
                                      return Padding(
                                        padding: const EdgeInsets.only(bottom: 4),
                                        child: Row(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            const Text('• ', style: TextStyle(fontWeight: FontWeight.bold)),
                                            Expanded(
                                              child: Text(
                                                h.toString(),
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                  height: 1.4,
                                                  color: AppColors.textSecondary,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      );
                                    }),
                                  ],
                                ],
                              ),
                            );
                          }),
                          const SizedBox(height: 16),
                        ],

                        // Education
                        if (education.isNotEmpty) ...[
                          const Text(
                            'Education',
                            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 10),
                          ...education.map((edu) {
                            final deg = edu['degree'] ?? 'Degree';
                            final inst = edu['institution'] ?? '';
                            final yr = edu['year'] ?? '';

                            return Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: Row(
                                children: [
                                  CircleAvatar(
                                    radius: 16,
                                    backgroundColor: AppColors.secondary.withValues(alpha: 0.1),
                                    child: const Icon(Icons.school, size: 16, color: AppColors.secondary),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          deg.toString(),
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                        ),
                                        if (inst.toString().isNotEmpty)
                                          Text(
                                            inst.toString(),
                                            style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                                          ),
                                      ],
                                    ),
                                  ),
                                  if (yr.toString().isNotEmpty)
                                    Text(
                                      yr.toString(),
                                      style: const TextStyle(fontSize: 11, color: AppColors.textLight),
                                    ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _confirmDelete(MultilingualResumeModel resume) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Text(resume.languageFlag, style: const TextStyle(fontSize: 22)),
            const SizedBox(width: 8),
            Text('Delete ${resume.languageName} Resume?'),
          ],
        ),
        content: const Text(
          'Are you sure you want to remove this translated resume from your vault? This action cannot be undone.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.error,
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              Navigator.pop(ctx);
              final ok = await ref
                  .read(multilingualResumeProvider.notifier)
                  .deleteResume(resume.id);
              if (ok && mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Resume deleted successfully.'),
                    backgroundColor: AppColors.success,
                  ),
                );
              }
            },
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(multilingualResumeProvider);
    final presets = state.presets.isNotEmpty
        ? state.presets.map((p) => {
            'name': p['name']?.toString() ?? '',
            'code': p['code']?.toString() ?? '',
            'flag': p['flag']?.toString() ?? '🌐',
            'native': p['native']?.toString() ?? '',
          }).toList()
        : _fallbackPresets;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Multilingual Resumes'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
            onPressed: () => ref.read(multilingualResumeProvider.notifier).fetchResumes(),
          ),
        ],
      ),
      body: state.isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () => ref.read(multilingualResumeProvider.notifier).fetchResumes(),
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Hero Banner
                    Container(
                      padding: const EdgeInsets.all(18),
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
                                child: const Icon(Icons.language, color: Colors.white, size: 24),
                              ),
                              const SizedBox(width: 12),
                              const Expanded(
                                child: Text(
                                  'Global Career Reach',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Translate your ATS resume into 16+ native languages or any world tongue. Technical terms, certifications, and formatting remain 100% accurate.',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                              height: 1.45,
                            ),
                          ),
                          if (!state.hasBaseResume) ...[
                            const SizedBox(height: 12),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.amber.shade400,
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.info_outline, size: 16, color: Colors.black87),
                                  SizedBox(width: 6),
                                  Text(
                                    'Upload your base resume first to unlock AI translation.',
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.black87,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Generator Card
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.border),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.03),
                            blurRadius: 10,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.tune, color: AppColors.primary, size: 20),
                              const SizedBox(width: 8),
                              const Text(
                                'Select Target Languages',
                                style: TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              const Spacer(),
                              Text(
                                '${_selectedLanguages.length} selected',
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.primary,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),

                          // Quick Bundle Filters
                          Wrap(
                            spacing: 8,
                            children: [
                              ActionChip(
                                label: const Text('⭐ Top 5 Global', style: TextStyle(fontSize: 11)),
                                onPressed: () => _selectBundle(['German', 'Spanish', 'French', 'Japanese', 'Mandarin Chinese']),
                              ),
                              ActionChip(
                                label: const Text('🇪🇺 European', style: TextStyle(fontSize: 11)),
                                onPressed: () => _selectBundle(['German', 'French', 'Spanish', 'Italian', 'Dutch', 'Portuguese']),
                              ),
                              if (_selectedLanguages.isNotEmpty)
                                ActionChip(
                                  label: const Text('Clear All', style: TextStyle(fontSize: 11, color: AppColors.error)),
                                  onPressed: _clearLanguages,
                                ),
                            ],
                          ),
                          const SizedBox(height: 14),

                          // Preset Chips Grid
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: presets.map((p) {
                              final name = p['name'] ?? '';
                              final flag = p['flag'] ?? '🌐';
                              final isSelected = _selectedLanguages.contains(name);

                              return FilterChip(
                                label: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Text(flag, style: const TextStyle(fontSize: 14)),
                                    const SizedBox(width: 6),
                                    Text(name, style: const TextStyle(fontSize: 12)),
                                  ],
                                ),
                                selected: isSelected,
                                selectedColor: AppColors.primary.withValues(alpha: 0.15),
                                checkmarkColor: AppColors.primary,
                                labelStyle: TextStyle(
                                  color: isSelected ? AppColors.primary : AppColors.textPrimary,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                ),
                                side: BorderSide(
                                  color: isSelected ? AppColors.primary : Colors.grey.shade300,
                                ),
                                onSelected: (_) => _toggleLanguage(name),
                              );
                            }).toList(),
                          ),
                          const SizedBox(height: 16),

                          // Custom Language Input
                          Row(
                            children: [
                              Expanded(
                                child: CustomTextField(
                                  label: 'Any Other Language',
                                  hint: 'e.g. Swedish, Greek, Korean',
                                  controller: _customLangController,
                                  prefixIcon: Icons.edit_note,
                                ),
                              ),
                              const SizedBox(width: 8),
                              Padding(
                                padding: const EdgeInsets.only(top: 14),
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.secondary,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                  onPressed: _addCustomLanguage,
                                  child: const Icon(Icons.add, size: 20),
                                ),
                              ),
                            ],
                          ),

                          // Show non-preset selected languages as dismissible tags
                          if (_selectedLanguages.any((lang) => !presets.any((p) => p['name'] == lang))) ...[
                            const SizedBox(height: 10),
                            Wrap(
                              spacing: 6,
                              children: _selectedLanguages
                                  .where((lang) => !presets.any((p) => p['name'] == lang))
                                  .map((lang) {
                                return Chip(
                                  label: Text(lang, style: const TextStyle(fontSize: 12)),
                                  deleteIcon: const Icon(Icons.close, size: 14),
                                  onDeleted: () => _toggleLanguage(lang),
                                  backgroundColor: Colors.purple.shade50,
                                );
                              }).toList(),
                            ),
                          ],

                          const SizedBox(height: 16),

                          // Template Selector
                          const Text(
                            'ATS Resume Template',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: DropdownButtonHideUnderline(
                              child: DropdownButton<int>(
                                value: _selectedTemplateId,
                                isExpanded: true,
                                icon: const Icon(Icons.arrow_drop_down, color: AppColors.primary),
                                items: _templates.entries.map((e) {
                                  return DropdownMenuItem<int>(
                                    value: e.key,
                                    child: Text(e.value, style: const TextStyle(fontSize: 13)),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  if (val != null) setState(() => _selectedTemplateId = val);
                                },
                              ),
                            ),
                          ),
                          const SizedBox(height: 14),

                          // Target Role Input (Optional)
                          CustomTextField(
                            label: 'Target Role (Optional)',
                            hint: 'e.g. Senior Full Stack Engineer',
                            controller: _targetRoleController,
                            prefixIcon: Icons.badge_outlined,
                          ),
                          const SizedBox(height: 20),

                          // Generate Button
                          CustomButton(
                            text: state.isTranslating
                                ? 'Translating & Formatting...'
                                : 'Generate Multilingual Resume (${_selectedLanguages.length})',
                            icon: Icons.auto_awesome,
                            isLoading: state.isTranslating,
                            onPressed: state.isTranslating || !state.hasBaseResume
                                ? null
                                : _handleTranslate,
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 28),

                    // Resumes Vault Section
                    Row(
                      children: [
                        const Icon(Icons.folder_special_outlined, color: AppColors.primary, size: 22),
                        const SizedBox(width: 8),
                        const Text(
                          'Your Multilingual Vault',
                          style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.primary.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            '${state.resumes.length} translated',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: AppColors.primary,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    if (state.resumes.isEmpty)
                      Container(
                        padding: const EdgeInsets.all(28),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          children: [
                            CircleAvatar(
                              radius: 30,
                              backgroundColor: Colors.grey.shade100,
                              child: const Icon(Icons.translate, size: 32, color: Colors.grey),
                            ),
                            const SizedBox(height: 14),
                            const Text(
                              'No Multilingual Resumes Yet',
                              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 6),
                            const Text(
                              'Select one or more languages above to generate your first localized ATS resume!',
                              textAlign: TextAlign.center,
                              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      )
                    else
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: state.resumes.length,
                        itemBuilder: (ctx, idx) {
                          final item = state.resumes[idx];
                          return Container(
                            margin: const EdgeInsets.only(bottom: 14),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: AppColors.border),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.03),
                                  blurRadius: 8,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Text(item.languageFlag, style: const TextStyle(fontSize: 26)),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item.languageName,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 15,
                                            ),
                                          ),
                                          Text(
                                            'Template #${item.templateId} • ${item.createdAt.length >= 10 ? item.createdAt.substring(0, 10) : ""}',
                                            style: const TextStyle(
                                              fontSize: 11,
                                              color: AppColors.textSecondary,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    // Template badge
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: AppColors.primary.withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        'T#${item.templateId}',
                                        style: const TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                if (item.jobTitle.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Text(
                                    item.jobTitle,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                ],
                                if (item.summarySnippet.isNotEmpty) ...[
                                  const SizedBox(height: 6),
                                  Text(
                                    item.summarySnippet,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      color: AppColors.textSecondary,
                                      height: 1.4,
                                    ),
                                  ),
                                ],
                                const SizedBox(height: 12),
                                // Metrics chips
                                Row(
                                  children: [
                                    if (item.skillsCount > 0)
                                      Container(
                                        margin: const EdgeInsets.only(right: 8),
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.blue.shade50,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          '${item.skillsCount} Skills',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: Colors.blue.shade700,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    if (item.experienceCount > 0)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: Colors.purple.shade50,
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          '${item.experienceCount} Experience',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: Colors.purple.shade700,
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    const Spacer(),
                                    // Action buttons
                                    OutlinedButton.icon(
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                        visualDensity: VisualDensity.compact,
                                        side: const BorderSide(color: AppColors.primary),
                                      ),
                                      icon: const Icon(Icons.visibility_outlined, size: 14, color: AppColors.primary),
                                      label: const Text('Preview', style: TextStyle(fontSize: 11, color: AppColors.primary)),
                                      onPressed: () => _showResumePreviewSheet(item),
                                    ),
                                    const SizedBox(width: 6),
                                    IconButton(
                                      icon: const Icon(Icons.delete_outline, size: 18, color: AppColors.error),
                                      tooltip: 'Delete',
                                      visualDensity: VisualDensity.compact,
                                      onPressed: () => _confirmDelete(item),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                  ],
                ),
              ),
            ),
    );
  }
}

