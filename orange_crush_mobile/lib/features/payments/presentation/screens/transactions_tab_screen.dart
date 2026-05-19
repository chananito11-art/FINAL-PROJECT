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

class TransactionsTabScreen extends StatefulWidget {
  const TransactionsTabScreen({super.key});

  @override
  State<TransactionsTabScreen> createState() => _TransactionsTabScreenState();
}

class _TransactionsTabScreenState extends State<TransactionsTabScreen> {
  List<Map<String, dynamic>> _transactions = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  Future<void> _loadTransactions() async {
    setState(() => _isLoading = true);
    final list = await ApiService.fetchTransactions();
    if (mounted) {
      setState(() {
        _transactions = list;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.darkBg,
      appBar: AppBar(
        title: const Text('Transaction History', style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTransactions,
          )
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.orange))
          : RefreshIndicator(
              onRefresh: _loadTransactions,
              color: AppTheme.orange,
              child: _transactions.isEmpty
                  ? ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: [
                        SizedBox(height: MediaQuery.of(context).size.height * 0.2),
                        const Center(
                          child: Icon(
                            Icons.receipt_long_outlined,
                            size: 64,
                            color: AppTheme.muted,
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Center(
                          child: Text(
                            'No transaction history found.',
                            style: TextStyle(color: AppTheme.muted, fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    )
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      itemCount: _transactions.length,
                      itemBuilder: (context, index) {
                        final tx = _transactions[index];
                        final double amount = (tx['amount_submitted'] ?? tx['amount'] ?? 0.0) as double;
                        return Card(
                          margin: const EdgeInsets.only(bottom: 16),
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                // Date & Status Row
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.calendar_today_rounded, size: 14, color: AppTheme.muted),
                                        const SizedBox(width: 6),
                                        Text(
                                          tx['date'] ?? '',
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.muted),
                                        ),
                                      ],
                                    ),
                                    _buildTxStatusBadge(tx['status'] ?? 'pending'),
                                  ],
                                ),
                                const Divider(height: 24, color: AppTheme.line),

                                // Vehicle Details Row
                                Row(
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
                                      child: Image.network(
                                        tx['vehicle_image_url'] ?? '',
                                        width: 70,
                                        height: 52,
                                        fit: BoxFit.cover,
                                        errorBuilder: (context, error, stackTrace) =>
                                            Container(color: AppTheme.darkCard, width: 70, height: 52, child: const Icon(Icons.image_not_supported)),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            tx['vehicle_name'] ?? 'Car Rental',
                                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Booking #${tx['booking_id']}',
                                            style: const TextStyle(color: AppTheme.muted, fontSize: 12),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Column(
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        Text(
                                          '₱${amount.toStringAsFixed(2)}',
                                          style: const TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.w900,
                                            color: AppTheme.orangeLight,
                                          ),
                                        ),
                                        const Text(
                                          'Submitted',
                                          style: TextStyle(color: AppTheme.muted, fontSize: 9),
                                        ),
                                      ],
                                    )
                                  ],
                                ),
                                const SizedBox(height: 16),

                                // GCash details block
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: AppTheme.darkBg,
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(color: AppTheme.line),
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.stretch,
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          const Text(
                                            'GCash Account',
                                            style: TextStyle(fontSize: 11, color: AppTheme.muted),
                                          ),
                                          Text(
                                            tx['gcash_account_name'] ?? 'N/A',
                                            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.text),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          const Text(
                                            'GCash Ref #',
                                            style: TextStyle(fontSize: 11, color: AppTheme.muted),
                                          ),
                                          Text(
                                            tx['gcash_transaction_reference_number'] ?? 'N/A',
                                            style: const TextStyle(fontSize: 11, fontFamily: 'monospace', fontWeight: FontWeight.bold, color: AppTheme.orangeLight),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 6),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          const Text(
                                            'System Ref Code',
                                            style: TextStyle(fontSize: 11, color: AppTheme.muted),
                                          ),
                                          Text(
                                            tx['reference_code'] ?? 'N/A',
                                            style: const TextStyle(fontSize: 11, fontFamily: 'monospace', color: AppTheme.textDim),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }

  Widget _buildTxStatusBadge(String status) {
    Color bg;
    Color fg;
    switch (status.toLowerCase()) {
      case 'verified':
      case 'approved':
        bg = AppTheme.green.withOpacity(0.08);
        fg = AppTheme.green;
        break;
      case 'pending':
        bg = AppTheme.yellow.withOpacity(0.08);
        fg = AppTheme.yellow;
        break;
      case 'rejected':
        bg = AppTheme.red.withOpacity(0.08);
        fg = AppTheme.red;
        break;
      case 'refunded':
        bg = Colors.blue.withOpacity(0.08);
        fg = Colors.blue;
        break;
      default:
        bg = AppTheme.line;
        fg = AppTheme.text;
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
      child: Text(
        status.toUpperCase(),
        style: TextStyle(color: fg, fontWeight: FontWeight.bold, fontSize: 10),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 3. FLEET CATALOG SCREEN (HOME)
// ─────────────────────────────────────────────────────────────────────────────
