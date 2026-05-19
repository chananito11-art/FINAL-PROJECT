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

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController(text: 'customer@orangecrush.com');
  final _passwordController = TextEditingController(text: 'password');
  final _gatewayController = TextEditingController(text: ApiService.baseUrl);
  bool _isLoading = false;
  bool _showSettings = false;
  bool _rememberMe = false;

  void _handleLogin() async {
    setState(() {
      _isLoading = true;
      ApiService.baseUrl = _gatewayController.text.trim();
    });

    final success = await ApiService.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (success) {
      // Load initial state
      final profile = await ApiService.fetchProfile();
      if (profile != null) {
        sessionUser.firstName = profile['first_name'] ?? '';
        sessionUser.lastName = profile['last_name'] ?? '';
        sessionUser.email = profile['email'] ?? '';
        sessionUser.phone = profile['phone'] ?? '';
        sessionUser.licenseNumber = profile['license_number'] ?? '';
        sessionUser.kycStatus = profile['verification_status'] ?? 'unverified';
      }

      // Load bookings
      final bookings = await ApiService.fetchBookings();
      setState(() {
        userBookings = bookings;
        _isLoading = false;
      });

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const MainNavigationScreen()),
      );
    } else {
      setState(() => _isLoading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('❌ Invalid email or password.'),
          backgroundColor: AppTheme.red,
        ),
      );
    }
  }

  InputDecoration _buildInputDecoration(String label, String hint) {
    return InputDecoration(
      labelText: label,
      hintText: hint,
      labelStyle: const TextStyle(color: AppTheme.orangeLight, fontSize: 14, fontWeight: FontWeight.bold),
      hintStyle: const TextStyle(color: AppTheme.muted, fontSize: 13),
      floatingLabelBehavior: FloatingLabelBehavior.always,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppTheme.line),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppTheme.orange, width: 2),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(16),
        borderSide: const BorderSide(color: AppTheme.line),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.darkBg,
      body: Stack(
        children: [
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 28.0, vertical: 16.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const SizedBox(height: 20),
                    // Logo
                    const Icon(Icons.directions_car_rounded, size: 72, color: AppTheme.orange),
                    const SizedBox(height: 12),
                    const Text(
                      'ORANGE CRUSH',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.w900,
                        letterSpacing: -1,
                        color: AppTheme.text,
                      ),
                    ),
                    const Text(
                      'Premium Self-Drive Rentals',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 14,
                        color: AppTheme.orangeLight,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 36),

                    // Config Gateway Panel (if expanded)
                    if (_showSettings) ...[
                      Container(
                        margin: const EdgeInsets.only(bottom: 24),
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppTheme.darkCard,
                          border: Border.all(color: AppTheme.line),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('API GATEWAY URL', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.orangeLight)),
                            const SizedBox(height: 6),
                            TextField(
                              controller: _gatewayController,
                              style: const TextStyle(fontSize: 13, color: AppTheme.orangeLight),
                              decoration: const InputDecoration(hintText: 'e.g. http://10.0.2.2:8000/api'),
                            ),
                          ],
                        ),
                      ),
                    ],

                    // Email Input
                    TextField(
                      controller: _emailController,
                      style: const TextStyle(color: AppTheme.text),
                      decoration: _buildInputDecoration('E-mail', 'example@email.com'),
                    ),
                    const SizedBox(height: 20),

                    // Password Input
                    TextField(
                      controller: _passwordController,
                      obscureText: true,
                      style: const TextStyle(color: AppTheme.text),
                      decoration: _buildInputDecoration('Password', 'Your Password'),
                    ),
                    const SizedBox(height: 16),

                    // Remember Me & Forgot Password Row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            SizedBox(
                              width: 24,
                              height: 24,
                              child: Checkbox(
                                value: _rememberMe,
                                activeColor: AppTheme.orange,
                                checkColor: Colors.white,
                                side: const BorderSide(color: AppTheme.line, width: 1.5),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                onChanged: (val) {
                                  setState(() {
                                    _rememberMe = val ?? false;
                                  });
                                },
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'Remember me',
                              style: TextStyle(color: AppTheme.muted, fontSize: 13, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                        TextButton(
                          onPressed: () {
                            showDialog(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: const Text('🔒 Reset Password'),
                                content: const Text(
                                  'For security reasons, password resets must be requested directly via your web portal account or by reaching out to operations support at support@orangecrush.com.',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.pop(context),
                                    child: const Text('Understood', style: TextStyle(color: AppTheme.orange)),
                                  ),
                                ],
                              ),
                            );
                          },
                          child: const Text(
                            'Forgot Password?',
                            style: TextStyle(color: AppTheme.muted, fontSize: 13, fontWeight: FontWeight.w600),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Primary Login Button
                    ElevatedButton(
                      onPressed: _isLoading ? null : _handleLogin,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.orange,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                        elevation: 0,
                      ),
                      child: _isLoading
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Text('Login', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 36),

                    // or sign up with divider
                    Row(
                      children: [
                        const Expanded(child: Divider(color: AppTheme.line, thickness: 1)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            'or sign up with',
                            style: TextStyle(color: AppTheme.muted.withValues(alpha: 0.8), fontSize: 13, fontWeight: FontWeight.w600),
                          ),
                        ),
                        const Expanded(child: Divider(color: AppTheme.line, thickness: 1)),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Social Icons Row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: [
                        _buildSocialIconCard(
                          const Icon(Icons.facebook, color: Color(0xFF1877F2), size: 28),
                        ),
                        _buildSocialIconCard(
                          ShaderMask(
                            shaderCallback: (bounds) => const LinearGradient(
                              colors: [Colors.red, Colors.yellow, Colors.green, Colors.blue],
                            ).createShader(bounds),
                            child: const Text('G', style: TextStyle(fontSize: 26, fontWeight: FontWeight.w900, color: Colors.white)),
                          ),
                        ),
                        _buildSocialIconCard(
                          const Icon(Icons.apple, color: Colors.white, size: 28),
                        ),
                      ],
                    ),
                    const SizedBox(height: 32),

                    // Just Sign Up Row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Text("Don't have an account? ", style: TextStyle(color: AppTheme.muted, fontWeight: FontWeight.w500)),
                        GestureDetector(
                          onTap: () {
                            showDialog(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: const Text('📝 Create Account'),
                                content: const Text(
                                  'To guarantee KYC safety compliance, new customer registration must be completed on the OrangeCrush Web Portal or by contacting our administrative customer desk.',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () => Navigator.pop(context),
                                    child: const Text('Understood', style: TextStyle(color: AppTheme.orange)),
                                  ),
                                ],
                              ),
                            );
                          },
                          child: const Text(
                            "Sign Up",
                            style: TextStyle(
                              color: AppTheme.orangeLight,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
          ),
          
          // Gear Settings Icon placed high up on the top right cleanly
          Positioned(
            top: 40,
            right: 16,
            child: SafeArea(
              child: IconButton(
                icon: Icon(
                  _showSettings ? Icons.settings_applications : Icons.settings,
                  color: AppTheme.muted,
                  size: 24,
                ),
                onPressed: () {
                  setState(() {
                    _showSettings = !_showSettings;
                  });
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSocialIconCard(Widget child) {
    return Container(
      width: 90,
      height: 55,
      decoration: BoxDecoration(
        color: AppTheme.darkCard,
        border: Border.all(color: AppTheme.line),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Center(child: child),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 2. MAIN BOTTOM NAVIGATION WRAPPER (Saves full module parity)
// ─────────────────────────────────────────────────────────────────────────────
