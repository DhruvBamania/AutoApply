import 'package:flutter/foundation.dart';

class ApiEndpoints {
  /// Live Production Base URL:
  /// Once your Vercel site is deployed, paste your live URL here:
  /// e.g. 'https://ai-looping.vercel.app/api/v1'
  /// Or compile with: flutter build apk --dart-define=API_BASE_URL=https://your-domain.vercel.app/api/v1
  static const String liveBaseUrl = 'https://auto-apply-two-phi.vercel.app/api/v1';

  /// Set to true to connect mobile app directly to live Vercel backend
  static const bool isProduction = true;

  /// Local development IP for Android physical devices on Wi-Fi
  static const String pcLanIp = '192.168.1.7';

  /// Dynamically resolves the API base URL:
  /// 1. Uses --dart-define=API_BASE_URL=... if passed at compile time
  /// 2. Uses liveBaseUrl if isProduction is true or if liveBaseUrl is customized
  /// 3. Falls back to local LAN IP / localhost for local development
  static String get baseUrl {
    const String envDefinedUrl = String.fromEnvironment('API_BASE_URL');
    if (envDefinedUrl.isNotEmpty) {
      return envDefinedUrl.endsWith('/')
          ? envDefinedUrl.substring(0, envDefinedUrl.length - 1)
          : envDefinedUrl;
    }

    if (isProduction || (!liveBaseUrl.contains('YOUR_LIVE_VERCEL_DOMAIN') && liveBaseUrl.isNotEmpty)) {
      return liveBaseUrl.endsWith('/')
          ? liveBaseUrl.substring(0, liveBaseUrl.length - 1)
          : liveBaseUrl;
    }

    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api/v1';
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return 'http://$pcLanIp:8000/api/v1';
      default:
        return 'http://127.0.0.1:8000/api/v1';
    }
  }

  // --- Auth Endpoints ---
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String me = '/auth/me';
  static const String logout = '/auth/logout';

  // --- Company Endpoints ---
  static const String companyJobs = '/company/jobs';
  static String companyJobDetail(int id) => '/company/jobs/$id';
  static String companyJobRepost(int id) => '/company/jobs/$id/repost';
  static String repostJob(int id) => '/company/jobs/$id/repost';
  static String deleteJob(int id) => '/company/jobs/$id';

  // Gemini AI Candidate Matching & Leaderboard
  static String jobMatchCandidates(int id) => '/company/jobs/$id/ai-match';
  static String companyJobLeaderboard(int id) => '/company/jobs/$id/leaderboard';
  static String batchShortlistCandidates(int id) => '/company/jobs/$id/ai-shortlist-top';

  // Inquiries & Query Pinning Hub
  static const String companyInquiries = '/company/inquiries';
  static String togglePin(int id) => '/company/inquiries/$id/pin';
  static String updateInquiryStatus(int id) => '/company/inquiries/$id/status';
  static String replyInquiry(int id) => '/company/inquiries/$id/reply';
  static const String syncLinkedIn = '/company/inquiries/sync';
  static const String companyInquirySync = '/company/inquiries/sync';
  static const String companyInquiryManual = '/company/inquiries/manual';

  // --- Candidate Endpoints ---
  static const String candidateResumes = '/candidate/resume';
  static const String candidateResume = '/candidate/resume';
  static const String candidateResumeUpload = '/candidate/resume/upload';
  static const String tailorResume = '/candidate/resume/tailor';
  static const String resumeTemplates = '/candidate/templates';
  static const String candidateTemplates = '/candidate/templates';
  static const String candidateResumeExport = '/candidate/resume/export';

  static const String candidateCampaigns = '/candidate/campaigns';
  static String candidateCampaignGenerate(int id) => '/candidate/campaigns/$id/generate';
  static const String candidateSocialPosts = '/candidate/social-posts';
  static const String candidateSocialPostsGenerate = '/candidate/social-posts/generate';
  static String candidateSocialPostApprove(int id) => '/candidate/social-posts/$id/approve';

  // --- AI Dynamic Interview Preparation Endpoints ---
  static const String interviewPreps = '/candidate/interview-prep';
  static String interviewPrepDetail(int id) => '/candidate/interview-prep/$id';
  static const String generateInterviewPrep = '/candidate/interview-prep/generate';
  static const String interviewPrepProfile = '/candidate/interview-prep/profile';
  static String toggleBookmarkPrep(int id) => '/candidate/interview-prep/$id/bookmark';
  static String deleteInterviewPrep(int id) => '/candidate/interview-prep/$id';

  // --- AI Headhunter Agent Endpoints ---
  // Company Sourcing & Outreach
  static String jobHeadhunterMatches(int id) => '/company/jobs/$id/headhunter';
  static String jobHeadhunterScout(int id) => '/company/jobs/$id/headhunter/scout';
  static String headhunterInvite(int id) => '/company/headhunter/$id/invite';
  static String jobHeadhunterBatchInvite(int id) => '/company/jobs/$id/headhunter/batch-invite';

  // Candidate Scouted Opportunities
  static const String candidateHeadhunterInvitations = '/candidate/headhunter/invitations';
  static String candidateHeadhunterRespond(int id) => '/candidate/headhunter/invitations/$id/respond';

  // --- Public Job Discovery Endpoints ---
  static const String publicJobs = '/jobs';
  static String publicJobDetail(int id) => '/jobs/$id';
  static String applyToJob(int id) => '/jobs/$id/apply';

  // --- Public Inbound Contact Form Endpoint ---
  static const String contact = '/contact';
}
