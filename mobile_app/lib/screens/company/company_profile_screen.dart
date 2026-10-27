import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/custom_button.dart';
import '../auth/login_screen.dart';

/// CompanyProfileScreen: Employer Profile & Account Settings
class CompanyProfileScreen extends ConsumerWidget {
  const CompanyProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authProvider).user;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Company Profile'),
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Company Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 36,
                    backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                    child: const Icon(Icons.business, size: 36, color: AppColors.primary),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    user?.companyName ?? user?.name ?? 'Company',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    user?.email ?? '',
                    style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
                  ),
                  if (user?.companyWebsite != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      user!.companyWebsite!,
                      style: const TextStyle(color: AppColors.primary, fontSize: 13),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 20),

            // WhatsApp Business Line Card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFF25D366).withValues(alpha: 0.3)),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF25D366).withValues(alpha: 0.05),
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
                          color: const Color(0xFF25D366).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(Icons.chat_bubble_rounded, color: Color(0xFF25D366), size: 20),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'WhatsApp Inquiries',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            Text(
                              'Candidate inquiries route directly to this WhatsApp line',
                              style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFBBF7D0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.phone_android, size: 16, color: Color(0xFF16A34A)),
                            const SizedBox(width: 8),
                            Text(
                              user?.whatsappNumber?.isNotEmpty == true
                                  ? user!.whatsappNumber!
                                  : 'No WhatsApp number set',
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                                color: user?.whatsappNumber?.isNotEmpty == true
                                    ? const Color(0xFF166534)
                                    : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                        TextButton.icon(
                          onPressed: () => _showEditWhatsAppDialog(context, ref, user?.whatsappNumber),
                          icon: const Icon(Icons.edit, size: 14, color: Color(0xFF16A34A)),
                          label: Text(
                            user?.whatsappNumber?.isNotEmpty == true ? 'Edit' : 'Set Up',
                            style: const TextStyle(
                              color: Color(0xFF16A34A),
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Connected Platform Integrations Status
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
                  const Text(
                    'Multi-Posting & Messaging Channels',
                    style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  _buildChannelItem(
                    name: 'WhatsApp Candidate Hotline',
                    icon: Icons.chat_bubble_outline_rounded,
                    color: const Color(0xFF25D366),
                    status: user?.whatsappNumber != null ? 'Connected' : 'Active (Direct)',
                  ),
                  const Divider(),
                  _buildChannelItem(
                    name: 'LinkedIn Jobs & Comments',
                    icon: Icons.business_center,
                    color: AppColors.linkedin,
                    status: 'Connected & Active',
                  ),
                  const Divider(),
                  _buildChannelItem(
                    name: 'Naukri Employer',
                    icon: Icons.work,
                    color: AppColors.naukri,
                    status: 'Connected & Active',
                  ),
                  const Divider(),
                  _buildChannelItem(
                    name: 'Indeed Employer',
                    icon: Icons.search,
                    color: AppColors.indeed,
                    status: 'Connected & Active',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),

            // Sign out button
            CustomButton(
              text: 'Sign Out',
              icon: Icons.logout,
              backgroundColor: AppColors.error,
              onPressed: () async {
                await ref.read(authProvider.notifier).logout();
                if (context.mounted) {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(builder: (_) => const LoginScreen()),
                    (route) => false,
                  );
                }
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildChannelItem({
    required String name,
    required IconData icon,
    required Color color,
    required String status,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          CircleAvatar(
            radius: 16,
            backgroundColor: color.withValues(alpha: 0.12),
            child: Icon(icon, size: 16, color: color),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              name,
              style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 14),
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: AppColors.success.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              status,
              style: const TextStyle(
                color: AppColors.success,
                fontSize: 11,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showEditWhatsAppDialog(BuildContext context, WidgetRef ref, String? currentNumber) {
    final controller = TextEditingController(text: currentNumber ?? '');

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: const Color(0xFF25D366).withValues(alpha: 0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(Icons.chat_bubble_rounded, color: Color(0xFF25D366), size: 20),
            ),
            const SizedBox(width: 10),
            const Text('WhatsApp Business', style: TextStyle(fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Enter your official WhatsApp contact number with country code. Candidates will route direct inquiries to this line.',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: controller,
              keyboardType: TextInputType.phone,
              decoration: InputDecoration(
                labelText: 'WhatsApp Number (E.164)',
                hintText: '+1 555 123 4567 or +91 9876543210',
                prefixIcon: const Icon(Icons.phone, size: 18),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF25D366),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              final newNumber = controller.text.trim();
              Navigator.pop(ctx);
              final success = await ref.read(authProvider.notifier).updateCompanyWhatsApp(
                    newNumber.isEmpty ? null : newNumber,
                  );
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(
                      success
                          ? '✅ WhatsApp number updated successfully!'
                          : 'Failed to update WhatsApp number.',
                    ),
                    backgroundColor: success ? AppColors.success : AppColors.error,
                  ),
                );
              }
            },
            child: const Text('Save Number', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }
}
