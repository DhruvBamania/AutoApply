import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/company_jobs_provider.dart';
import '../../widgets/custom_button.dart';
import '../../widgets/custom_text_field.dart';

/// CreateJobScreen: 1-Click Multi-Platform Job Posting with Flyer Upload
/// 
/// Allows employers to simultaneously publish job vacancies to
/// LinkedIn, Naukri, and Indeed with high-converting flyers.
class CreateJobScreen extends ConsumerStatefulWidget {
  const CreateJobScreen({super.key});

  @override
  ConsumerState<CreateJobScreen> createState() => _CreateJobScreenState();
}

class _CreateJobScreenState extends ConsumerState<CreateJobScreen> {
  final _formKey = GlobalKey<FormState>();

  final _titleController = TextEditingController();
  final _companyController = TextEditingController();
  final _locationController = TextEditingController();
  final _salaryController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _requirementsController = TextEditingController();

  String _jobType = 'Full-time';
  String _experienceLevel = 'Mid Level';

  // Platforms to cross-post to (all 3 selected by default)
  final Set<String> _selectedPlatforms = {'linkedin', 'naukri', 'indeed'};

  // Flyer attachment
  PlatformFile? _flyerFile;

  @override
  void initState() {
    super.initState();
    // Pre-populate company name from logged in user profile
    final user = ref.read(authProvider).user;
    if (user != null) {
      _companyController.text = user.companyName ?? user.name;
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _companyController.dispose();
    _locationController.dispose();
    _salaryController.dispose();
    _descriptionController.dispose();
    _requirementsController.dispose();
    super.dispose();
  }

  Future<void> _pickFlyerImage() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.image,
        withData: kIsWeb, // Loads bytes for web
      );

      if (result != null && result.files.isNotEmpty) {
        setState(() {
          _flyerFile = result.files.first;
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to pick image: $e')),
      );
    }
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedPlatforms.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select at least 1 platform to post to!'),
          backgroundColor: AppColors.error,
        ),
      );
      return;
    }

    final success = await ref.read(companyJobsProvider.notifier).createJob(
          title: _titleController.text.trim(),
          companyName: _companyController.text.trim(),
          location: _locationController.text.trim(),
          jobType: _jobType,
          experienceLevel: _experienceLevel,
          salaryRange: _salaryController.text.trim(),
          description: _descriptionController.text.trim(),
          requirements: _requirementsController.text.trim(),
          platforms: _selectedPlatforms.toList(),
          flyerFilePath: _flyerFile?.path,
          flyerFileName: _flyerFile?.name,
          flyerFileBytes: _flyerFile?.bytes,
        );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Job published to ${_selectedPlatforms.length} platforms with live comment sync!',
          ),
          backgroundColor: AppColors.success,
        ),
      );

      // Reset form
      _titleController.clear();
      _locationController.clear();
      _salaryController.clear();
      _descriptionController.clear();
      _requirementsController.clear();
      setState(() => _flyerFile = null);
    } else if (mounted) {
      final err = ref.read(companyJobsProvider).errorMessage ?? 'Posting failed';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(err), backgroundColor: AppColors.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final jobsState = ref.watch(companyJobsProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('1-Click Multi-Platform Post'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Target Platforms Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.share, size: 18, color: AppColors.primary),
                        SizedBox(width: 8),
                        Text(
                          'Publish Simultaneously To',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        _buildPlatformCheckbox('linkedin', 'LinkedIn', AppColors.linkedin),
                        _buildPlatformCheckbox('naukri', 'Naukri', AppColors.naukri),
                        _buildPlatformCheckbox('indeed', 'Indeed', AppColors.indeed),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Job Details Form Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CustomTextField(
                      label: 'Job Title',
                      hint: 'e.g. Lead Flutter & AI Engineer',
                      controller: _titleController,
                      prefixIcon: Icons.work_outline,
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Title is required' : null,
                    ),
                    const SizedBox(height: 14),

                    CustomTextField(
                      label: 'Company Name',
                      hint: 'e.g. TechCorp Solutions',
                      controller: _companyController,
                      prefixIcon: Icons.apartment,
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Company is required' : null,
                    ),
                    const SizedBox(height: 14),

                    Row(
                      children: [
                        Expanded(
                          child: CustomTextField(
                            label: 'Location',
                            hint: 'e.g. Remote / Bangalore',
                            controller: _locationController,
                            prefixIcon: Icons.location_on_outlined,
                            validator: (v) => (v == null || v.trim().isEmpty) ? 'Location required' : null,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: CustomTextField(
                            label: 'Salary Range',
                            hint: 'e.g. ₹15L - ₹25L',
                            controller: _salaryController,
                            prefixIcon: Icons.payments_outlined,
                            validator: (v) => (v == null || v.trim().isEmpty) ? 'Salary required' : null,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    // Job Type & Experience Dropdowns
                    Row(
                      children: [
                        Expanded(
                          child: _buildDropdown(
                            label: 'Job Type',
                            value: _jobType,
                            items: ['Full-time', 'Part-time', 'Contract', 'Remote', 'Internship'],
                            onChanged: (v) => setState(() => _jobType = v!),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _buildDropdown(
                            label: 'Experience Level',
                            value: _experienceLevel,
                            items: ['Entry Level', 'Mid Level', 'Senior', 'Lead', 'Executive'],
                            onChanged: (v) => setState(() => _experienceLevel = v!),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),

                    CustomTextField(
                      label: 'Job Description',
                      hint: 'Describe the key responsibilities, role overview, and team context...',
                      controller: _descriptionController,
                      maxLines: 4,
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Description required' : null,
                    ),
                    const SizedBox(height: 14),

                    CustomTextField(
                      label: 'Skills & Requirements',
                      hint: 'List required skills (e.g. Flutter, Dart, REST APIs, Laravel, Gemini AI)...',
                      controller: _requirementsController,
                      maxLines: 3,
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Requirements required' : null,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Flyer Upload Card
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.image, size: 18, color: AppColors.primary),
                        SizedBox(width: 8),
                        Text(
                          'Job Poster / Flyer (Optional)',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Attaching a visual banner increases LinkedIn and Naukri engagement by 3x',
                      style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 12),
                    if (_flyerFile == null)
                      OutlinedButton.icon(
                        onPressed: _pickFlyerImage,
                        icon: const Icon(Icons.file_upload_outlined),
                        label: const Text('Pick Image (PNG/JPG)'),
                        style: OutlinedButton.styleFrom(
                          minimumSize: const Size(double.infinity, 46),
                          side: const BorderSide(color: AppColors.primary),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.check_circle, color: AppColors.success, size: 20),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                _flyerFile!.name,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            IconButton(
                              icon: const Icon(Icons.close, size: 18, color: AppColors.error),
                              onPressed: () => setState(() => _flyerFile = null),
                            ),
                          ],
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Submit Button
              CustomButton(
                text: 'Publish to ${_selectedPlatforms.length} Platform(s)',
                icon: Icons.rocket_launch,
                isLoading: jobsState.isSubmitting,
                onPressed: _handleSubmit,
              ),
              const SizedBox(height: 24),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPlatformCheckbox(String key, String label, Color color) {
    final isChecked = _selectedPlatforms.contains(key);
    return Expanded(
      child: GestureDetector(
        onTap: () {
          setState(() {
            if (isChecked) {
              _selectedPlatforms.remove(key);
            } else {
              _selectedPlatforms.add(key);
            }
          });
        },
        child: Container(
          margin: const EdgeInsets.symmetric(horizontal: 4),
          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
          decoration: BoxDecoration(
            color: isChecked ? color.withValues(alpha: 0.12) : Colors.grey.shade100,
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: isChecked ? color : Colors.grey.shade300, width: 1.5),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                isChecked ? Icons.check_box : Icons.check_box_outline_blank,
                size: 16,
                color: isChecked ? color : AppColors.textLight,
              ),
              const SizedBox(width: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isChecked ? color : AppColors.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDropdown({
    required String label,
    required String value,
    required List<String> items,
    required Function(String?) onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
        ),
        const SizedBox(height: 6),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: AppColors.border),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              isExpanded: true,
              value: value,
              items: items.map((i) => DropdownMenuItem(value: i, child: Text(i, style: const TextStyle(fontSize: 13)))).toList(),
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }
}
