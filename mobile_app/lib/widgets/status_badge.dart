import 'package:flutter/material.dart';
import '../core/constants/app_colors.dart';

/// Reusable Badge for Platforms (LinkedIn, Naukri, Indeed) and Application Statuses
class StatusBadge extends StatelessWidget {
  final String label;
  final Color backgroundColor;
  final Color textColor;
  final IconData? icon;

  const StatusBadge({
    super.key,
    required this.label,
    required this.backgroundColor,
    required this.textColor,
    this.icon,
  });

  /// Factory for Platform badges (LinkedIn, Naukri, Indeed, Direct)
  factory StatusBadge.platform(String platform) {
    Color bg;
    Color text;
    String name;

    switch (platform.toLowerCase()) {
      case 'linkedin':
        bg = AppColors.linkedin.withValues(alpha: 0.12);
        text = AppColors.linkedin;
        name = 'LinkedIn';
        break;
      case 'naukri':
        bg = AppColors.naukri.withValues(alpha: 0.12);
        text = AppColors.naukri;
        name = 'Naukri';
        break;
      case 'indeed':
        bg = AppColors.indeed.withValues(alpha: 0.12);
        text = AppColors.indeed;
        name = 'Indeed';
        break;
      case 'whatsapp':
        bg = const Color(0xFF25D366).withValues(alpha: 0.15);
        text = const Color(0xFF15803d);
        name = 'WhatsApp';
        break;
      default:
        bg = AppColors.primary.withValues(alpha: 0.1);
        text = AppColors.primary;
        name = 'Direct Web';
    }

    return StatusBadge(
      label: name,
      backgroundColor: bg,
      textColor: text,
      icon: Icons.public,
    );
  }

  /// Factory for Status badges (new, shortlisted, reviewed, rejected)
  factory StatusBadge.status(String status) {
    Color bg;
    Color text;
    String name;

    switch (status.toLowerCase()) {
      case 'shortlisted':
        bg = AppColors.success.withValues(alpha: 0.12);
        text = AppColors.success;
        name = 'Shortlisted';
        break;
      case 'reviewed':
        bg = AppColors.info.withValues(alpha: 0.12);
        text = AppColors.info;
        name = 'Reviewed';
        break;
      case 'rejected':
        bg = AppColors.error.withValues(alpha: 0.12);
        text = AppColors.error;
        name = 'Rejected';
        break;
      default:
        bg = AppColors.warning.withValues(alpha: 0.15);
        text = AppColors.warning;
        name = 'New';
    }

    return StatusBadge(
      label: name,
      backgroundColor: bg,
      textColor: text,
    );
  }

  /// Factory for Pin badge
  factory StatusBadge.pinned() {
    return const StatusBadge(
      label: 'Pinned to Top',
      backgroundColor: Color(0xFFFEF3C7),
      textColor: Color(0xFFB45309),
      icon: Icons.push_pin,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 12, color: textColor),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: TextStyle(
              color: textColor,
              fontWeight: FontWeight.w600,
              fontSize: 11,
            ),
          ),
        ],
      ),
    );
  }
}
