import 'package:flutter/material.dart';
import 'package:orange_crush_mobile/core/theme/app_theme.dart';
import 'package:orange_crush_mobile/shared/models/models.dart';
import 'package:orange_crush_mobile/core/services/api_service.dart';
import 'package:orange_crush_mobile/core/constants/globals.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:io';

import 'package:orange_crush_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:orange_crush_mobile/features/dashboard/presentation/screens/main_navigation_screen.dart';
import 'package:orange_crush_mobile/features/dashboard/presentation/screens/dashboard_tab_screen.dart';
import 'package:orange_crush_mobile/features/payments/presentation/screens/transactions_tab_screen.dart';
import 'package:orange_crush_mobile/features/cars/presentation/screens/fleet_catalog_screen.dart';
import 'package:orange_crush_mobile/features/cars/presentation/screens/vehicle_detail_screen.dart';
import 'package:orange_crush_mobile/features/bookings/presentation/screens/rentals_dashboard_screen.dart';
import 'package:orange_crush_mobile/features/dashboard/presentation/screens/tracking_milestone_screen.dart';
import 'package:orange_crush_mobile/features/auth/presentation/screens/profile_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  const MainNavigationScreen({super.key});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = [
    const DashboardTabScreen(),
    const FleetCatalogScreen(),
    const RentalsDashboardScreen(),
    const TransactionsTabScreen(),
    const ProfileScreen(),
  ];

  void setIndex(int index) {
    setState(() => _currentIndex = index);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        selectedItemColor: AppTheme.orange,
        unselectedItemColor: AppTheme.muted,
        backgroundColor: AppTheme.dark,
        elevation: 16,
        type: BottomNavigationBarType.fixed,
        onTap: setIndex,
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.dashboard_rounded), label: 'Dashboard'),
          BottomNavigationBarItem(icon: Icon(Icons.directions_car), label: 'Fleet'),
          BottomNavigationBarItem(icon: Icon(Icons.receipt_long), label: 'Rentals'),
          BottomNavigationBarItem(icon: Icon(Icons.account_balance_wallet_rounded), label: 'Payments'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 2.1 DASHBOARD TAB SCREEN
// ─────────────────────────────────────────────────────────────────────────────
