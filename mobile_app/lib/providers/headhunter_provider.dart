import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/headhunter_match_model.dart';

class HeadhunterState {
  final bool isLoading;
  final bool isScouting;
  final bool isInviting;
  final List<HeadhunterMatchModel> companyMatches;
  final List<HeadhunterMatchModel> candidateInvitations;
  final String? errorMessage;
  final String? successMessage;

  const HeadhunterState({
    this.isLoading = false,
    this.isScouting = false,
    this.isInviting = false,
    this.companyMatches = const [],
    this.candidateInvitations = const [],
    this.errorMessage,
    this.successMessage,
  });

  HeadhunterState copyWith({
    bool? isLoading,
    bool? isScouting,
    bool? isInviting,
    List<HeadhunterMatchModel>? companyMatches,
    List<HeadhunterMatchModel>? candidateInvitations,
    String? errorMessage,
    String? successMessage,
    bool clearMessages = false,
  }) {
    return HeadhunterState(
      isLoading: isLoading ?? this.isLoading,
      isScouting: isScouting ?? this.isScouting,
      isInviting: isInviting ?? this.isInviting,
      companyMatches: companyMatches ?? this.companyMatches,
      candidateInvitations: candidateInvitations ?? this.candidateInvitations,
      errorMessage: clearMessages ? null : (errorMessage ?? this.errorMessage),
      successMessage: clearMessages ? null : (successMessage ?? this.successMessage),
    );
  }
}

class HeadhunterNotifier extends StateNotifier<HeadhunterState> {
  HeadhunterNotifier() : super(const HeadhunterState());

  final _api = ApiClient();

  /// 1. Fetch Headhunter Matches for a Company's Job Posting
  Future<void> fetchJobMatches(int jobId) async {
    state = state.copyWith(isLoading: true, clearMessages: true);
    try {
      final response = await _api.dio.get(ApiEndpoints.jobHeadhunterMatches(jobId));
      if (response.data['success'] == true || response.statusCode == 200) {
        final list = (response.data['data'] as List? ?? [])
            .map((e) => HeadhunterMatchModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        state = state.copyWith(isLoading: false, companyMatches: list);
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load headhunter matches',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Network error loading matches: $e',
      );
    }
  }

  /// 2. Autonomous Scout Trigger for Company Job
  Future<bool> scoutJob(int jobId, {int limit = 6}) async {
    state = state.copyWith(isScouting: true, clearMessages: true);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.jobHeadhunterScout(jobId),
        data: {'limit': limit},
      );

      if (response.data['success'] == true || response.statusCode == 200) {
        final list = (response.data['data'] as List? ?? [])
            .map((e) => HeadhunterMatchModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        final msg = response.data['message'] ?? '🎯 AI Headhunter successfully scouted top candidates!';
        state = state.copyWith(
          isScouting: false,
          companyMatches: list,
          successMessage: msg,
        );
        return true;
      } else {
        state = state.copyWith(
          isScouting: false,
          errorMessage: response.data['message'] ?? 'Scouting completed with no new matches',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isScouting: false,
        errorMessage: 'Error during AI headhunter scout: $e',
      );
      return false;
    }
  }

  /// 3. Send Outreach Invitation to Sourced Candidate
  Future<bool> sendInvitation(
    int matchId, {
    String? subject,
    String? message,
  }) async {
    state = state.copyWith(isInviting: true, clearMessages: true);
    try {
      final payload = <String, dynamic>{};
      if (subject != null) payload['outreach_subject'] = subject;
      if (message != null) payload['outreach_message'] = message;

      final response = await _api.dio.post(
        ApiEndpoints.headhunterInvite(matchId),
        data: payload,
      );

      if (response.data['success'] == true || response.statusCode == 200) {
        final updatedData = response.data['data'];
        final updatedMatch = HeadhunterMatchModel.fromJson(Map<String, dynamic>.from(updatedData));

        final updatedList = state.companyMatches.map((m) {
          return m.id == matchId ? updatedMatch : m;
        }).toList();

        state = state.copyWith(
          isInviting: false,
          companyMatches: updatedList,
          successMessage: '✉️ Headhunter invitation sent successfully!',
        );
        return true;
      } else {
        state = state.copyWith(
          isInviting: false,
          errorMessage: response.data['message'] ?? 'Failed to send invitation',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isInviting: false,
        errorMessage: 'Error sending invitation: $e',
      );
      return false;
    }
  }

  /// 4. Batch Invite Top Sourced Candidates
  Future<bool> batchInvite(int jobId, {int minScore = 80}) async {
    state = state.copyWith(isInviting: true, clearMessages: true);
    try {
      final response = await _api.dio.post(
        ApiEndpoints.jobHeadhunterBatchInvite(jobId),
        data: {'min_score': minScore},
      );

      if (response.data['success'] == true || response.statusCode == 200) {
        final count = response.data['invited_count'] ?? 0;
        await fetchJobMatches(jobId);
        state = state.copyWith(
          isInviting: false,
          successMessage: '🚀 Successfully invited $count top candidates (≥$minScore% Match)!',
        );
        return true;
      } else {
        state = state.copyWith(
          isInviting: false,
          errorMessage: response.data['message'] ?? 'No uninvited candidates matched threshold',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isInviting: false,
        errorMessage: 'Batch invitation failed: $e',
      );
      return false;
    }
  }

  /// 5. Candidate: Fetch Scouted Invitations
  Future<void> fetchCandidateInvitations() async {
    state = state.copyWith(isLoading: true, clearMessages: true);
    try {
      final response = await _api.dio.get(ApiEndpoints.candidateHeadhunterInvitations);
      if (response.data['success'] == true || response.statusCode == 200) {
        final list = (response.data['data'] as List? ?? [])
            .map((e) => HeadhunterMatchModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();

        state = state.copyWith(isLoading: false, candidateInvitations: list);
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to load scouted invitations',
        );
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Network error loading invitations: $e',
      );
    }
  }

  /// 6. Candidate: Respond to Scouted Opportunity (Accept & Connect or Decline)
  Future<bool> respondToInvitation(
    int matchId,
    String action, {
    String? note,
  }) async {
    state = state.copyWith(isLoading: true, clearMessages: true);
    try {
      final payload = <String, dynamic>{
        'action': action,
      };
      if (note != null) payload['note'] = note;

      final response = await _api.dio.post(
        ApiEndpoints.candidateHeadhunterRespond(matchId),
        data: payload,
      );

      if (response.data['success'] == true || response.statusCode == 200) {
        final updatedData = response.data['data'];
        final updatedMatch = HeadhunterMatchModel.fromJson(Map<String, dynamic>.from(updatedData));

        final updatedList = state.candidateInvitations.map((m) {
          return m.id == matchId ? updatedMatch : m;
        }).toList();

        final msg = action == 'accepted'
            ? '🎉 Invitation accepted! You are now connected with the employer and added to their shortlist.'
            : 'Invitation response recorded.';

        state = state.copyWith(
          isLoading: false,
          candidateInvitations: updatedList,
          successMessage: msg,
        );
        return true;
      } else {
        state = state.copyWith(
          isLoading: false,
          errorMessage: response.data['message'] ?? 'Failed to respond to invitation',
        );
        return false;
      }
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Error responding to invitation: $e',
      );
      return false;
    }
  }

  void clearMessages() {
    state = state.copyWith(clearMessages: true);
  }
}

final headhunterProvider = StateNotifierProvider<HeadhunterNotifier, HeadhunterState>(
  (ref) => HeadhunterNotifier(),
);
