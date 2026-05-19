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

class DashboardTabScreen extends StatefulWidget {
  const DashboardTabScreen({super.key});

  @override
  State<DashboardTabScreen> createState() => _DashboardTabScreenState();
}

class _DashboardTabScreenState extends State<DashboardTabScreen> {
  Map<String, dynamic>? _dashboardData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);
    final data = await ApiService.fetchDashboard();
    if (mounted) {
      setState(() {
        _dashboardData = data;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: AppTheme.orange)),
      );
    }

    final stats = _dashboardData?['stats'] ?? {};
    final recentBookings = (_dashboardData?['recent_bookings'] as List<Booking>?) ?? [];
    final recommendedVehicles = (_dashboardData?['recommended_vehicles'] as List<Vehicle>?) ?? [];

    return Scaffold(
      appBar: AppBar(
        title: const Text('OrangeCrush Hub', style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDashboard,
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadDashboard,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(20.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Hero Welcome Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppTheme.orange, AppTheme.orangeLight],
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.orange.withValues(alpha: 0.24),
                      blurRadius: 16,
                      offset: const Offset(0, 8),
                    )
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Hello, ${sessionUser.firstName}!',
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900, color: Colors.white),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      sessionUser.kycStatus == 'verified' 
                          ? '✅ Verified Driver Profile' 
                          : '⚠️ Complete your KYC verification under the Profile tab.',
                      style: const TextStyle(fontSize: 13, color: Colors.white70, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Stats Grid
              const Text('OPERATIONAL SUMMARY', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
              const SizedBox(height: 12),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.5,
                children: [
                  _buildStatCard('Active Rentals', '${stats['active_rentals'] ?? 0}', Icons.vpn_key_rounded, AppTheme.orange),
                  _buildStatCard('Pending Approvals', '${stats['pending_approval'] ?? 0}', Icons.hourglass_top_rounded, AppTheme.yellow),
                  _buildStatCard('Total Bookings', '${stats['total_bookings'] ?? 0}', Icons.history_rounded, Colors.blue),
                  _buildStatCard('Total Spent', '₱${(stats['total_spent'] ?? 0).toStringAsFixed(0)}', Icons.payment_rounded, AppTheme.green),
                ],
              ),
              const SizedBox(height: 24),

              // Recommended Vehicles Section
              if (recommendedVehicles.isNotEmpty) ...[
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('RECOMMENDED FLEET', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
                    TextButton(
                      onPressed: () {
                        // Change bottom tab to Fleet index (1)
                        final navState = context.findAncestorStateOfType<_MainNavigationScreenState>();
                        if (navState != null) {
                          navState.setState(() => navState._currentIndex = 1);
                        }
                      },
                      child: const Text('View All', style: TextStyle(color: AppTheme.orangeLight, fontSize: 11)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                SizedBox(
                  height: 170,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: recommendedVehicles.length,
                    itemBuilder: (context, index) {
                      final vehicle = recommendedVehicles[index];
                      return Container(
                        width: 220,
                        margin: const EdgeInsets.only(right: 14),
                        child: Card(
                          margin: EdgeInsets.zero,
                          child: InkWell(
                            borderRadius: BorderRadius.circular(16),
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(builder: (context) => VehicleDetailScreen(vehicle: vehicle)),
                              ).then((_) => _loadDashboard());
                            },
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                ClipRRect(
                                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                                  child: Image.network(
                                    vehicle.imageUrl,
                                    height: 100,
                                    fit: BoxFit.cover,
                                    errorBuilder: (context, error, stackTrace) =>
                                        Container(color: AppTheme.darkCard, height: 100, child: const Icon(Icons.image_not_supported)),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.all(10.0),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          vehicle.name,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                      Text(
                                        '₱${vehicle.pricePerDay.toStringAsFixed(0)}',
                                        style: const TextStyle(color: AppTheme.orangeLight, fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ],
                                  ),
                                )
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 24),
              ],

              // Recent Bookings Section
              if (recentBookings.isNotEmpty) ...[
                const Text('RECENT RESERVATIONS', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
                const SizedBox(height: 12),
                ListView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: recentBookings.length,
                  itemBuilder: (context, index) {
                    final booking = recentBookings[index];
                    return Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        leading: ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            booking.vehicle.imageUrl,
                            width: 60,
                            height: 45,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) =>
                                Container(color: AppTheme.darkCard, width: 60, height: 45, child: const Icon(Icons.image_not_supported)),
                          ),
                        ),
                        title: Text(booking.vehicle.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                        subtitle: Text(
                          'Rent: ${booking.pickupDate.month}/${booking.pickupDate.day} - ${booking.returnDate.month}/${booking.returnDate.day}',
                          style: const TextStyle(fontSize: 11, color: AppTheme.muted),
                        ),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 12, color: AppTheme.muted),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => TrackingMilestoneScreen(booking: booking)),
                          ).then((_) => _loadDashboard());
                        },
                      ),
                    );
                  },
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.darkCard,
        border: Border.all(color: AppTheme.line),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Icon(icon, color: color, size: 20),
              Container(
                width: 6,
                height: 6,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
              )
            ],
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                value,
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.text),
              ),
              const SizedBox(height: 2),
              Text(
                label,
                style: const TextStyle(fontSize: 10, color: AppTheme.muted, fontWeight: FontWeight.w600),
              ),
            ],
          )
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 2.2 PAYMENTS/TRANSACTIONS HISTORY TAB SCREEN
// ─────────────────────────────────────────────────────────────────────────────
