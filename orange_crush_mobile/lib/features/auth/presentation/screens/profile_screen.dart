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

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _licController = TextEditingController(text: sessionUser.licenseNumber);
  bool _isUploading = false;

  void _submitKyc() async {
    if (_licController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Driver license number is required.'),
          backgroundColor: AppTheme.yellow,
        ),
      );
      return;
    }

    final picker = ImagePicker();
    final XFile? image = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
    );

    if (image == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Clear photo of your Driver\'s License is required.'),
          backgroundColor: AppTheme.yellow,
        ),
      );
      return;
    }

    setState(() => _isUploading = true);

    // Auto-compute expiration date set 3 years into the future for demo simplicity
    final expirationDate = DateTime.now().add(const Duration(days: 365 * 3));
    final expirationStr = "${expirationDate.year}-${expirationDate.month.toString().padLeft(2, '0')}-${expirationDate.day.toString().padLeft(2, '0')}";

    final success = await ApiService.submitVerification(
      _licController.text.trim(),
      expirationStr,
      image.path,
    );

    if (!mounted) return;
    setState(() => _isUploading = false);

    if (success) {
      setState(() {
        sessionUser.licenseNumber = _licController.text.trim();
        sessionUser.kycStatus = 'pending';
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('✅ Driver license documents uploaded! Admin will audit them shortly.'),
          backgroundColor: AppTheme.green,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('❌ KYC upload failed. Please try again.'),
          backgroundColor: AppTheme.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile', style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.transparent,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Center(
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: AppTheme.orange.withOpacity(0.12),
                    child: Text(
                      sessionUser.firstName.isNotEmpty ? sessionUser.firstName[0].toUpperCase() : 'U',
                      style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900, color: AppTheme.orangeLight),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text('${sessionUser.firstName} ${sessionUser.lastName}', style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                  Text(sessionUser.email, style: const TextStyle(color: AppTheme.muted, fontSize: 13)),
                ],
              ),
            ),
            const SizedBox(height: 32),

            const Text('PERSONAL DETAILS', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
            const SizedBox(height: 12),
            _buildInfoTile('Phone Number', sessionUser.phone.isNotEmpty ? sessionUser.phone : 'N/A'),
            const Divider(color: AppTheme.line),

            const SizedBox(height: 24),

            const Text('LICENSE KYC VERIFICATION', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.darkCard,
                border: Border.all(color: AppTheme.line),
                borderRadius: BorderRadius.circular(18),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('KYC Driver Status', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                      _buildKycBadge(sessionUser.kycStatus),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (sessionUser.kycStatus == 'unverified') ...[
                    const Text(
                      'Please input your driver license number and upload a clear photo of your ID to complete verify process before booking vehicles.',
                      style: TextStyle(color: AppTheme.muted, fontSize: 12),
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      controller: _licController,
                      decoration: const InputDecoration(hintText: 'e.g. N01-23-456789'),
                    ),
                    const SizedBox(height: 16),
                    ElevatedButton.icon(
                      onPressed: _isUploading ? null : _submitKyc,
                      icon: const Icon(Icons.upload_file, size: 16),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.orange,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      label: const Text('Pick Image & Upload License'),
                    ),
                  ] else if (sessionUser.kycStatus == 'pending') ...[
                    const Row(
                      children: [
                        Icon(Icons.hourglass_empty, color: AppTheme.yellow, size: 18),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Your driver profile documentation is currently under review by our admin operations team. This usually takes 5-10 minutes.',
                            style: TextStyle(color: AppTheme.muted, fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                  ] else ...[
                    Row(
                      children: [
                        const Icon(Icons.verified, color: AppTheme.green, size: 20),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('License Number: ${sessionUser.licenseNumber}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                              const SizedBox(height: 2),
                              const Text('Verified Self-Drive Renter Account', style: TextStyle(color: AppTheme.muted, fontSize: 11)),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ]
                ],
              ),
            ),
            const SizedBox(height: 48),

            ElevatedButton(
              onPressed: () async {
                await ApiService.logout();
                if (!mounted) return;
                Navigator.pushReplacement(
                  context,
                  MaterialPageRoute(builder: (context) => const LoginScreen()),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.darkCard,
                side: const BorderSide(color: AppTheme.red),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Log Out Session', style: TextStyle(color: AppTheme.red, fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoTile(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppTheme.muted)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildKycBadge(String status) {
    Color bg;
    Color fg;
    String label;
    switch (status) {
      case 'unverified':
        bg = AppTheme.red.withOpacity(0.12);
        fg = AppTheme.red;
        label = 'UNVERIFIED';
        break;
      case 'pending':
        bg = AppTheme.yellow.withOpacity(0.12);
        fg = AppTheme.yellow;
        label = 'UNDER REVIEW';
        break;
      case 'verified':
        bg = AppTheme.green.withOpacity(0.12);
        fg = AppTheme.green;
        label = 'VERIFIED';
        break;
      default:
        bg = AppTheme.line;
        fg = AppTheme.text;
        label = 'STATUS';
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(8)),
      child: Text(
        label,
        style: TextStyle(color: fg, fontWeight: FontWeight.bold, fontSize: 10),
      ),
    );
  }
}
