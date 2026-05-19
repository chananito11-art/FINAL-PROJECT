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

class RentalsDashboardScreen extends StatefulWidget {
  const RentalsDashboardScreen({super.key});

  @override
  State<RentalsDashboardScreen> createState() => _RentalsDashboardScreenState();
}

class _RentalsDashboardScreenState extends State<RentalsDashboardScreen> {
  bool _isLoading = false;
  String _selectedTab = 'Upcoming';
  final List<String> _tabs = ['Upcoming', 'Ongoing', 'Past Rentals', 'Cancelled', 'No Show'];

  @override
  void initState() {
    super.initState();
    _loadBookings();
  }

  Future<void> _loadBookings() async {
    setState(() => _isLoading = true);
    final bookings = await ApiService.fetchBookings();
    if (mounted) {
      setState(() {
        userBookings = bookings;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final filteredBookings = userBookings.where((booking) {
      final status = booking.status.toLowerCase();
      switch (_selectedTab) {
        case 'Upcoming':
          return status == 'awaiting_approval' ||
              status == 'pending_payment' ||
              status == 'awaiting_verification' ||
              status == 'partial_paid' ||
              status == 'fully_paid' ||
              status == 'confirmed';
        case 'Ongoing':
          return status == 'ongoing';
        case 'Past Rentals':
          return status == 'completed';
        case 'Cancelled':
          return status == 'cancelled' || status == 'rejected';
        case 'No Show':
          return status == 'no_show';
        default:
          return false;
      }
    }).toList();

    return Scaffold(
      backgroundColor: AppTheme.darkBg,
      appBar: AppBar(
        title: const Text('My Bookings', style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadBookings,
          )
        ],
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // ── HORIZONTAL CUSTOM TABS BAR (Web Customer Parity) ──
          Container(
            height: 50,
            decoration: const BoxDecoration(
              border: Border(
                bottom: BorderSide(color: AppTheme.line, width: 1),
              ),
            ),
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _tabs.length,
              itemBuilder: (context, index) {
                final tab = _tabs[index];
                final isSelected = tab == _selectedTab;
                return GestureDetector(
                  onTap: () {
                    setState(() {
                      _selectedTab = tab;
                    });
                  },
                  child: Container(
                    margin: const EdgeInsets.only(right: 24),
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      border: Border(
                        bottom: BorderSide(
                          color: isSelected ? AppTheme.orange : Colors.transparent,
                          width: 2.5,
                        ),
                      ),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Text(
                      tab,
                      style: TextStyle(
                        color: isSelected ? AppTheme.orangeLight : AppTheme.muted,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                        fontSize: 14,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          // ── RENTALS LISTING VIEW ──
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppTheme.orange))
                : RefreshIndicator(
                    onRefresh: _loadBookings,
                    color: AppTheme.orange,
                    child: filteredBookings.isEmpty
                        ? ListView(
                            physics: const AlwaysScrollableScrollPhysics(),
                            children: [
                              SizedBox(height: MediaQuery.of(context).size.height * 0.15),
                              Center(
                                child: Container(
                                  width: 80,
                                  height: 80,
                                  decoration: BoxDecoration(
                                    color: AppTheme.darkCard,
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: const Icon(
                                    Icons.receipt_long_outlined,
                                    size: 40,
                                    color: AppTheme.muted,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 24),
                              Center(
                                child: Text(
                                  'No ${_selectedTab.toLowerCase()} bookings found.',
                                  style: const TextStyle(
                                    color: AppTheme.muted,
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Center(
                                child: TextButton(
                                  onPressed: () {
                                    context.findAncestorStateOfType<_MainNavigationScreenState>()?.setIndex(1);
                                  },
                                  child: const Text(
                                    'Browse available vehicles →',
                                    style: TextStyle(
                                      color: AppTheme.orangeLight,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          )
                        : ListView.builder(
                            physics: const AlwaysScrollableScrollPhysics(),
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                            itemCount: filteredBookings.length,
                            itemBuilder: (context, index) {
                              final booking = filteredBookings[index];
                              return Card(
                                margin: const EdgeInsets.only(bottom: 16),
                                child: Padding(
                                  padding: const EdgeInsets.all(16.0),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.stretch,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text('BOOKING #${booking.id}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.orangeLight)),
                                          _buildStatusBadge(booking.status),
                                        ],
                                      ),
                                      const SizedBox(height: 12),

                                      Row(
                                        children: [
                                          ClipRRect(
                                            borderRadius: BorderRadius.circular(8),
                                            child: Image.network(
                                              booking.vehicle.imageUrl,
                                              width: 80,
                                              height: 60,
                                              fit: BoxFit.cover,
                                              errorBuilder: (context, error, stackTrace) =>
                                                  Container(color: AppTheme.darkCard, width: 80, height: 60, child: const Icon(Icons.image_not_supported)),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(booking.vehicle.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                              const SizedBox(height: 2),
                                              Text(
                                                'Rent: ${booking.pickupDate.month}/${booking.pickupDate.day} to ${booking.returnDate.month}/${booking.returnDate.day}',
                                                style: const TextStyle(fontSize: 12, color: AppTheme.muted),
                                              ),
                                            ],
                                          )
                                        ],
                                      ),
                                      const SizedBox(height: 16),

                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              const Text('TOTAL OUTSTANDING', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
                                              const SizedBox(height: 2),
                                              Text('₱${booking.outstandingBalance.toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.red)),
                                            ],
                                          ),
                                          Column(
                                            crossAxisAlignment: CrossAxisAlignment.end,
                                            children: [
                                              const Text('SECURITY DEPOSIT', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
                                              const SizedBox(height: 2),
                                              Text('₱${booking.securityDeposit.toStringAsFixed(2)} (${booking.securityDepositStatus})',
                                                  style: TextStyle(fontWeight: FontWeight.bold, color: booking.securityDepositStatus == 'paid' ? AppTheme.green : AppTheme.yellow)),
                                            ],
                                          )
                                        ],
                                      ),
                                      const Divider(height: 32, color: AppTheme.line),

                                      ElevatedButton.icon(
                                        onPressed: () {
                                          Navigator.push(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) => TrackingMilestoneScreen(booking: booking),
                                            ),
                                          ).then((value) => _loadBookings());
                                        },
                                        icon: const Icon(Icons.location_searching_sharp, size: 16),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: AppTheme.darkCard,
                                          side: const BorderSide(color: AppTheme.line),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                        ),
                                        label: const Text('Track Live Status & Pay →', style: TextStyle(color: Colors.white, fontSize: 13)),
                                      )
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg;
    Color fg;
    switch (status) {
      case 'awaiting_approval':
        bg = Colors.blue.withOpacity(0.08);
        fg = Colors.blue;
        break;
      case 'pending_payment':
        bg = AppTheme.yellow.withOpacity(0.08);
        fg = AppTheme.yellow;
        break;
      case 'awaiting_verification':
        bg = Colors.purple.withOpacity(0.08);
        fg = Colors.purpleAccent;
        break;
      case 'partial_paid':
        bg = Colors.orange.withOpacity(0.08);
        fg = Colors.orange;
        break;
      case 'fully_paid':
      case 'confirmed':
        bg = AppTheme.green.withOpacity(0.08);
        fg = AppTheme.green;
        break;
      case 'ongoing':
        bg = AppTheme.orange.withOpacity(0.08);
        fg = AppTheme.orangeLight;
        break;
      case 'completed':
        bg = AppTheme.muted.withOpacity(0.08);
        fg = AppTheme.text;
        break;
      case 'cancelled':
      case 'rejected':
        bg = AppTheme.red.withOpacity(0.08);
        fg = AppTheme.red;
        break;
      case 'no_show':
        bg = Colors.redAccent.withOpacity(0.08);
        fg = Colors.redAccent;
        break;
      default:
        bg = AppTheme.line;
        fg = AppTheme.text;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
      child: Text(
        status.replaceAll('_', ' ').toUpperCase(),
        style: TextStyle(color: fg, fontWeight: FontWeight.bold, fontSize: 10),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 6. LIVE MILESTONE TRACKER & GCASH TRANSACTION Uploader SCREEN
// ─────────────────────────────────────────────────────────────────────────────
