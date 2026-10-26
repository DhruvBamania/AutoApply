import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';
import '../models/inquiry_model.dart';
import 'custom_button.dart';

/// Modal Bottom Sheet for shortlisting a candidate and scheduling an interview.
class ShortlistInterviewSheet extends StatefulWidget {
  final InquiryModel inquiry;
  final String? jobTitle;
  final Future<bool> Function({
    required DateTime scheduledAt,
    required String meetingType,
    String? meetingLink,
    String? notes,
    required bool sendEmail,
  }) onConfirm;

  const ShortlistInterviewSheet({
    super.key,
    required this.inquiry,
    this.jobTitle,
    required this.onConfirm,
  });

  /// Helper to display this bottom sheet from anywhere
  static Future<bool?> show(
    BuildContext context, {
    required InquiryModel inquiry,
    String? jobTitle,
    required Future<bool> Function({
      required DateTime scheduledAt,
      required String meetingType,
      String? meetingLink,
      String? notes,
      required bool sendEmail,
    }) onConfirm,
  }) {
    return showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => ShortlistInterviewSheet(
        inquiry: inquiry,
        jobTitle: jobTitle,
        onConfirm: onConfirm,
      ),
    );
  }

  @override
  State<ShortlistInterviewSheet> createState() => _ShortlistInterviewSheetState();
}

class _ShortlistInterviewSheetState extends State<ShortlistInterviewSheet> {
  late DateTime _selectedDate;
  late TimeOfDay _selectedTime;
  String _meetingType = 'google_meet';
  final _linkController = TextEditingController();
  final _notesController = TextEditingController();
  bool _sendEmail = true;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    // Default to tomorrow 10:00 AM or parse existing schedule
    if (widget.inquiry.interviewScheduledAt != null) {
      final parsed = DateTime.tryParse(widget.inquiry.interviewScheduledAt!);
      if (parsed != null) {
        _selectedDate = parsed;
        _selectedTime = TimeOfDay(hour: parsed.hour, minute: parsed.minute);
      } else {
        _initDefaultDateTime();
      }
    } else {
      _initDefaultDateTime();
    }

    if (widget.inquiry.meetingType != null && widget.inquiry.meetingType!.isNotEmpty) {
      _meetingType = widget.inquiry.meetingType!;
    }
    _linkController.text = widget.inquiry.meetingLinkOrLocation ?? '';
    _notesController.text = widget.inquiry.interviewNotes ?? '';

    // Check if candidate email exists
    if (widget.inquiry.candidateEmail.isEmpty) {
      _sendEmail = false;
    }
  }

  void _initDefaultDateTime() {
    final tomorrow = DateTime.now().add(const Duration(days: 1));
    _selectedDate = DateTime(tomorrow.year, tomorrow.month, tomorrow.day);
    _selectedTime = const TimeOfDay(hour: 10, minute: 0);
  }

  @override
  void dispose() {
    _linkController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate.isBefore(DateTime.now()) ? DateTime.now() : _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.primary,
              onPrimary: Colors.white,
              onSurface: AppColors.textPrimary,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _pickTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: _selectedTime,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: AppColors.primary,
              onPrimary: Colors.white,
              onSurface: AppColors.textPrimary,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedTime = picked);
    }
  }

  Future<void> _handleSubmit() async {
    final scheduledDateTime = DateTime(
      _selectedDate.year,
      _selectedDate.month,
      _selectedDate.day,
      _selectedTime.hour,
      _selectedTime.minute,
    );

    setState(() => _isSubmitting = true);
    final success = await widget.onConfirm(
      scheduledAt: scheduledDateTime,
      meetingType: _meetingType,
      meetingLink: _linkController.text.trim(),
      notes: _notesController.text.trim(),
      sendEmail: _sendEmail,
    );

    if (mounted) {
      setState(() => _isSubmitting = false);
      if (success) {
        Navigator.of(context).pop(true);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final hasEmail = widget.inquiry.candidateEmail.isNotEmpty;
    final dateString = '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';
    final timeString = _selectedTime.format(context);

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: EdgeInsets.only(
        top: 12,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 20,
      ),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Drag Handle
            Center(
              child: Container(
                width: 44,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Text('⭐', style: TextStyle(fontSize: 18)),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Shortlist & Schedule Interview',
                        style: TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.bold,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      Text(
                        'Auto-send email with interview details',
                        style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: AppColors.textSecondary),
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Candidate Summary Card
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 18,
                    backgroundColor: AppColors.primary.withValues(alpha: 0.12),
                    child: Text(
                      widget.inquiry.candidateName.isNotEmpty
                          ? widget.inquiry.candidateName[0].toUpperCase()
                          : 'C',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.inquiry.candidateName,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                        Text(
                          hasEmail ? widget.inquiry.candidateEmail : 'No email address on file',
                          style: TextStyle(
                            fontSize: 11,
                            color: hasEmail ? AppColors.textSecondary : Colors.amber.shade800,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Date & Time Picker Row
            const Text(
              'Interview Date & Time *',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: _pickDate,
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.border),
                        borderRadius: BorderRadius.circular(10),
                        color: Colors.white,
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today, size: 16, color: AppColors.primary),
                          const SizedBox(width: 8),
                          Text(dateString, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: InkWell(
                    onTap: _pickTime,
                    borderRadius: BorderRadius.circular(10),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.border),
                        borderRadius: BorderRadius.circular(10),
                        color: Colors.white,
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time, size: 16, color: AppColors.primary),
                          const SizedBox(width: 8),
                          Text(timeString, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            // Meeting Platform Dropdown
            const Text(
              'Meeting Mode / Platform',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.border),
                borderRadius: BorderRadius.circular(10),
                color: Colors.white,
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _meetingType,
                  isExpanded: true,
                  items: const [
                    DropdownMenuItem(value: 'google_meet', child: Text('💻 Google Meet (Online Video)')),
                    DropdownMenuItem(value: 'zoom', child: Text('🎥 Zoom Meeting')),
                    DropdownMenuItem(value: 'in_person', child: Text('🏢 In-Person (Office Visit)')),
                    DropdownMenuItem(value: 'phone', child: Text('📞 Phone Call')),
                  ],
                  onChanged: (val) {
                    if (val != null) setState(() => _meetingType = val);
                  },
                ),
              ),
            ),
            const SizedBox(height: 14),

            // Meeting Link / Address Field
            const Text(
              'Meeting Link or Location Address',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: _linkController,
              decoration: InputDecoration(
                hintText: 'e.g. https://meet.google.com/xyz or Office Address',
                hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade400),
                prefixIcon: const Icon(Icons.link, size: 18, color: AppColors.primary),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                ),
              ),
            ),
            const SizedBox(height: 14),

            // Recruiter Notes Field
            const Text(
              'Recruiter Notes / Instructions (Optional)',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
            ),
            const SizedBox(height: 6),
            TextField(
              controller: _notesController,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'e.g. Please keep your portfolio handy. The round will take ~45 mins.',
                hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade400),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
                ),
              ),
            ),
            const SizedBox(height: 14),

            // Automated Email Notification Card
            if (hasEmail)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFEEF2FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFC7D2FE)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.mark_email_read_outlined, size: 20, color: AppColors.primary),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Send Interview Email',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF3730A3)),
                          ),
                          Text(
                            'Candidate will be notified via Google SMTP immediately.',
                            style: TextStyle(fontSize: 10, color: Color(0xFF4338CA)),
                          ),
                        ],
                      ),
                    ),
                    Switch(
                      value: _sendEmail,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primary.withValues(alpha: 0.4),
                      onChanged: (val) => setState(() => _sendEmail = val),
                    ),
                  ],
                ),
              )
            else
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.info_outline, size: 18, color: Color(0xFFB45309)),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Candidate has no email on file. The schedule will be saved in dashboard only.',
                        style: TextStyle(fontSize: 11, color: Color(0xFF92400E)),
                      ),
                    ),
                  ],
                ),
              ),
            const SizedBox(height: 18),

            // Confirm Button
            CustomButton(
              text: '⭐ Confirm Shortlist & Send Email',
              isLoading: _isSubmitting,
              onPressed: _handleSubmit,
            ),
          ],
        ),
      ),
    );
  }
}
