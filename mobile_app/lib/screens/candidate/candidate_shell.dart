import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import 'job_discovery_screen.dart';
import 'resume_builder_screen.dart';
import 'interview_prep_screen.dart';
import 'candidate_scouted_screen.dart';
import 'candidate_profile_screen.dart';

/// CandidateShell: Bottom Navigation Container for Job Seekers / Candidates
class CandidateShell extends StatefulWidget {
  const CandidateShell({super.key});

  @override
  State<CandidateShell> createState() => _CandidateShellState();
}

class _CandidateShellState extends State<CandidateShell> {
  int _currentIndex = 0;

  final List<Widget> _screens = const [
    JobDiscoveryScreen(),
    ResumeBuilderScreen(),
    InterviewPrepScreen(),
    CandidateScoutedScreen(),
    CandidateProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) => setState(() => _currentIndex = index),
          selectedItemColor: AppColors.primary,
          unselectedItemColor: AppColors.textLight,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          elevation: 0,
          selectedFontSize: 11,
          unselectedFontSize: 11,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.explore_outlined),
              activeIcon: Icon(Icons.explore),
              label: 'Explore Jobs',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.auto_awesome_outlined),
              activeIcon: Icon(Icons.auto_awesome),
              label: 'AI Resume',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.psychology_outlined),
              activeIcon: Icon(Icons.psychology),
              label: 'Interview Prep',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.gps_fixed_outlined),
              activeIcon: Icon(Icons.gps_fixed),
              label: 'Scouted',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outline),
              activeIcon: Icon(Icons.person),
              label: 'Profile',
            ),
          ],
        ),
      ),
    );
  }
}
