import 'dart:async';
import 'package:app_links/app_links.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../providers/auth_provider.dart';
import '../../screens/auth/login_screen.dart';
import '../../screens/auth/register_screen.dart';
import '../../screens/candidate/candidate_shell.dart';
import '../../screens/company/company_shell.dart';
import '../../screens/company/headhunter_screen.dart';
import 'storage_service.dart';

/// DeepLinkService: Autonomous Deep Link & Universal Link Router
/// 
/// Handles both:
/// 1. Custom Schemes: `autoapply://...` and `ailooping://...`
/// 2. Universal App Links: `https://auto-apply-two-phi.vercel.app/open-app?...`
class DeepLinkService {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  static final AppLinks _appLinks = AppLinks();
  static StreamSubscription<Uri>? _linkSubscription;
  static bool _isInitialized = false;

  /// Initialize deep link listeners on app launch
  static Future<void> init(WidgetRef ref) async {
    if (_isInitialized) return;
    _isInitialized = true;

    // 1. Listen for background / foreground incoming deep links
    _linkSubscription = _appLinks.uriLinkStream.listen(
      (uri) {
        _handleUri(uri, ref);
      },
      onError: (err) {
        debugPrint('DeepLink error: $err');
      },
    );

    // 2. Check for cold-start initial link (app opened from killed state via deep link)
    try {
      final initialUri = await _appLinks.getInitialLink();
      if (initialUri != null) {
        // Delay slightly until first frame is rendered
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _handleUri(initialUri, ref);
        });
      }
    } catch (e) {
      debugPrint('Failed to get initial deep link: $e');
    }
  }

  /// Process incoming URI and navigate to matching screen
  static Future<void> _handleUri(Uri uri, WidgetRef ref) async {
    debugPrint('⚡ [DeepLinkService] Received: $uri');

    // 1. Handle Automatic Token Authentication if supplied in query params
    final token = uri.queryParameters['token'];
    final role = uri.queryParameters['role'] ?? 'user';
    if (token != null && token.isNotEmpty) {
      await StorageService.saveAuthData(
        token: token,
        role: role,
      );
      await ref.read(authProvider.notifier).checkAuthStatus();
    }

    // 2. Extract path and target
    String host = uri.host.toLowerCase();
    String path = uri.path.toLowerCase().replaceAll(RegExp(r'^/+|/+$'), '');
    final targetParam = uri.queryParameters['target']?.toLowerCase();
    final idParam = uri.queryParameters['id'];

    // Combine host + path for custom scheme vs universal link
    String fullRoute = '';
    if (uri.scheme == 'autoapply' || uri.scheme == 'ailooping') {
      fullRoute = host.isEmpty ? path : (path.isEmpty ? host : '$host/$path');
    } else {
      // Universal link like https://auto-apply-two-phi.vercel.app/open-app?target=job&id=12
      if (targetParam != null && targetParam.isNotEmpty) {
        fullRoute = idParam != null && idParam.isNotEmpty ? '$targetParam/$idParam' : targetParam;
      } else {
        fullRoute = path;
      }
    }

    _routeToDestination(fullRoute, uri.queryParameters, ref);
  }

  /// Resolve destination widget and push with global navigatorKey
  static void _routeToDestination(String route, Map<String, String> query, WidgetRef ref) {
    final nav = navigatorKey.currentState;
    if (nav == null) {
      debugPrint('DeepLink navigatorState not ready yet.');
      return;
    }

    final segments = route.split('/').where((s) => s.isNotEmpty).toList();
    final primary = segments.isNotEmpty ? segments[0] : '';
    final secondary = segments.length > 1 ? segments[1] : query['id'];

    debugPrint('⚡ [DeepLinkService] Routing -> Primary: "$primary", Secondary: "$secondary"');

    // Route 1: Jobs / Explore
    if (primary == 'jobs' || primary == 'job' || primary == 'explore') {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CandidateShell(initialIndex: 0)),
        (r) => false,
      );
      _showDeepLinkToast('Opened Explore Jobs');
      return;
    }

    // Route 2: AI Resume Builder
    if (primary == 'resumes' || primary == 'resume-builder' || primary == 'resume') {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CandidateShell(initialIndex: 1)),
        (r) => false,
      );
      _showDeepLinkToast('Opened AI Resume Builder');
      return;
    }

    // Route 3: AI Interview Prep
    if (primary == 'interview-prep' || primary == 'interview_prep' || primary == 'mock-interview' || primary == 'interview') {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CandidateShell(initialIndex: 2)),
        (r) => false,
      );
      _showDeepLinkToast('Opened AI Interview Prep');
      return;
    }

    // Route 4: Candidate Scouted Opportunities
    if (primary == 'scouted' || primary == 'headhunter-invitations' || primary == 'invitations') {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CandidateShell(initialIndex: 3)),
        (r) => false,
      );
      _showDeepLinkToast('Opened Scouted Opportunities');
      return;
    }

    // Route 5: Profile
    if (primary == 'profile') {
      final isCompany = ref.read(authProvider).isCompany;
      nav.pushAndRemoveUntil(
        MaterialPageRoute(
          builder: (_) => isCompany ? const CompanyShell(initialIndex: 3) : const CandidateShell(initialIndex: 4),
        ),
        (r) => false,
      );
      _showDeepLinkToast('Opened Profile');
      return;
    }

    // Route 6: Company Inquiries
    if (primary == 'inquiries' || (primary == 'company' && secondary == 'inquiries')) {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CompanyShell(initialIndex: 2)),
        (r) => false,
      );
      _showDeepLinkToast('Opened Recruiter Inquiries');
      return;
    }

    // Route 7: Company Create Job
    if (primary == 'create-job' || primary == 'post-job' || (primary == 'company' && secondary == 'create-job')) {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CompanyShell(initialIndex: 1)),
        (r) => false,
      );
      _showDeepLinkToast('Opened Post Job');
      return;
    }

    // Route 8: Company Dashboard
    if (primary == 'dashboard' || (primary == 'company' && (secondary == 'dashboard' || secondary == null))) {
      nav.pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const CompanyShell(initialIndex: 0)),
        (r) => false,
      );
      _showDeepLinkToast('Opened Recruiter Dashboard');
      return;
    }

    // Route 9: AI Headhunter Match
    if ((primary == 'company' && secondary == 'headhunter') || primary == 'headhunter') {
      final jobIdStr = segments.length > 2 ? segments[2] : query['job_id'];
      final jobId = int.tryParse(jobIdStr ?? '') ?? 0;
      if (jobId > 0) {
        nav.push(
          MaterialPageRoute(
            builder: (_) => HeadhunterScreen(jobId: jobId, jobTitle: 'Job Opening #$jobId'),
          ),
        );
        _showDeepLinkToast('Opened AI Headhunter Scout');
        return;
      }
    }

    // Route 10: Auth Login / Register
    if (primary == 'auth' || primary == 'login') {
      if (secondary == 'register') {
        nav.push(MaterialPageRoute(builder: (_) => const RegisterScreen()));
      } else {
        nav.push(MaterialPageRoute(builder: (_) => const LoginScreen()));
      }
      return;
    }

    // Default Fallback
    nav.pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const CandidateShell(initialIndex: 0)),
      (r) => false,
    );
  }

  /// Show quick feedback badge when deep link resolves
  static void _showDeepLinkToast(String message) {
    final context = navigatorKey.currentContext;
    if (context == null) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.bolt, color: Colors.amber, size: 20),
            const SizedBox(width: 8),
            Text(message, style: const TextStyle(fontWeight: FontWeight.bold)),
          ],
        ),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        backgroundColor: const Color(0xFF1E1B4B),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  /// Clean up subscription on dispose
  static void dispose() {
    _linkSubscription?.cancel();
    _isInitialized = false;
  }
}
