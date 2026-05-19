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

class TrackingMilestoneScreen extends StatefulWidget {
  final Booking booking;
  const TrackingMilestoneScreen({super.key, required this.booking});

  @override
  State<TrackingMilestoneScreen> createState() => _TrackingMilestoneScreenState();
}

class _TrackingMilestoneScreenState extends State<TrackingMilestoneScreen> {
  final _amountController = TextEditingController();
  final _refController = TextEditingController();
  bool _isPaying = false;

  void _submitGcash() async {
    if (_amountController.text.isEmpty || _refController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ All GCash reference and amount fields are required.'),
          backgroundColor: AppTheme.yellow,
        ),
      );
      return;
    }

    final double amount = double.tryParse(_amountController.text) ?? 0.0;
    if (amount <= 0) return;

    final picker = ImagePicker();
    final XFile? image = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
    );

    if (image == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Screenshot image of payment receipt is required.'),
          backgroundColor: AppTheme.yellow,
        ),
      );
      return;
    }

    setState(() => _isPaying = true);

    final result = await ApiService.submitPayment(
      widget.booking.id,
      amount,
      _refController.text.trim(),
      'GCash User',
      image.path,
    );

    if (!mounted) return;
    setState(() => _isPaying = false);

    if (result != null && result['success'] == true) {
      final Booking updatedBooking = result['booking'];
      setState(() {
        widget.booking.status = updatedBooking.status;
        widget.booking.payments.clear();
        widget.booking.payments.addAll(updatedBooking.payments);
      });

      Navigator.pop(context); // pop payment sheet
      Navigator.pop(context); // return to dashboard
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✅ GCash transaction uploaded! Admin will verify the reference soon.'),
          backgroundColor: AppTheme.green,
        ),
      );
    } else {
      final errorMsg = result?['message'] ?? 'Failed to submit payment.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ $errorMsg'),
          backgroundColor: AppTheme.red,
        ),
      );
    }
  }

  void _openPaymentSheet() {
    _amountController.text = widget.booking.outstandingBalance.toStringAsFixed(2);
    _refController.clear();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppTheme.darkBg,
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
          left: 20,
          right: 20,
          top: 20,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Upload GCash Payment', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.orangeLight)),
            const SizedBox(height: 16),
            const Text('CASH AMOUNT RECEIVED (PHP)', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
            const SizedBox(height: 6),
            TextField(
              controller: _amountController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(hintText: 'e.g. 5000.00'),
            ),
            const SizedBox(height: 16),
            const Text('GCASH REFERENCE NUMBER', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
            const SizedBox(height: 6),
            TextField(
              controller: _refController,
              decoration: const InputDecoration(hintText: 'e.g. 9021831203'),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _submitGcash,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.orange,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: _isPaying
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Pick Screenshot & Submit', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final booking = widget.booking;

    bool stage1 = true; // Booking placed
    bool stage2 = sessionUser.kycStatus == 'verified'; // KYC complete
    bool stage3 = booking.paidAmount > 0 || booking.status != 'pending_payment'; // Payment verified
    bool stage4 = booking.status == 'ongoing' || booking.status == 'completed'; // Handover complete
    bool stage5 = booking.status == 'completed'; // Settled

    return Scaffold(
      appBar: AppBar(
        title: const Text('Track Booking Milestones', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: Colors.transparent,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    Text('Milestone Tracking for Booking #${booking.id}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppTheme.muted)),
                    const SizedBox(height: 8),
                    Text(booking.vehicle.name, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            const Text('LIVE MILESTONES', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
            const SizedBox(height: 16),

            _buildMilestoneLine('1', 'Booking Placed', 'Your self-drive vehicle reservation is recorded in our system.', stage1),
            _buildMilestoneLine('2', 'KYC Driver License Verification', 'Admin will audit and verify your driver profile license details.', stage2),
            _buildMilestoneLine('3', 'Reservation / Security Payment', 'Ensure downpayment or security deposit is confirmed by finance.', stage3),
            _buildMilestoneLine('4', 'Vehicle Handover & Key Collection', 'Vehicle key has been handed over at the designated collection depot.', stage4),
            _buildMilestoneLine('5', 'Rental Completed & Deposit Settled', 'Return inspection passed and remaining outstanding deposit settled.', stage5),

            const Divider(height: 48, color: AppTheme.line),

            if (booking.outstandingBalance > 0 && booking.status == 'pending_payment') ...[
              ElevatedButton.icon(
                onPressed: _openPaymentSheet,
                icon: const Icon(Icons.payment, size: 18),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.orange,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                label: const Text('Send Partial/Full GCash Payment', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
              const SizedBox(height: 24),
            ],

            const Text('PAYMENT HISTORY LOGS', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
            const SizedBox(height: 12),
            if (booking.payments.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 8.0),
                child: Text('No payments recorded for this booking yet.', style: TextStyle(color: AppTheme.muted, fontSize: 13)),
              )
            else
              ...booking.payments.map((p) => ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text('₱${p.amount.toStringAsFixed(2)} via ${p.method.toUpperCase()}'),
                    subtitle: Text('${p.date} · ${p.notes}'),
                    trailing: Text(p.status.toUpperCase(), style: TextStyle(color: p.status == 'approved' || p.status == 'verified' ? AppTheme.green : AppTheme.yellow, fontWeight: FontWeight.bold, fontSize: 12)),
                  )),
          ],
        ),
      ),
    );
  }

  Widget _buildMilestoneLine(String step, String title, String subtitle, bool isCompleted) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: isCompleted ? AppTheme.green.withOpacity(0.15) : AppTheme.darkCard,
                shape: BoxShape.circle,
                border: Border.all(color: isCompleted ? AppTheme.green : AppTheme.line, width: 2),
              ),
              child: Center(
                child: isCompleted
                    ? const Icon(Icons.check, color: AppTheme.green, size: 16)
                    : Text(step, style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.muted)),
              ),
            ),
            Container(width: 2, height: 48, color: isCompleted ? AppTheme.green : AppTheme.line),
          ],
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: isCompleted ? AppTheme.text : AppTheme.muted)),
              const SizedBox(height: 2),
              Text(subtitle, style: const TextStyle(fontSize: 12, color: AppTheme.textDim)),
              const SizedBox(height: 20),
            ],
          ),
        )
      ],
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 7. PROFILE & KYC VERIFICATION SCREEN
// ─────────────────────────────────────────────────────────────────────────────
