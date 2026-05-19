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

class VehicleDetailScreen extends StatefulWidget {
  final Vehicle vehicle;
  final DateTime? initialPickupDate;
  final DateTime? initialReturnDate;
  const VehicleDetailScreen({
    super.key,
    required this.vehicle,
    this.initialPickupDate,
    this.initialReturnDate,
  });

  @override
  State<VehicleDetailScreen> createState() => _VehicleDetailScreenState();
}

class _VehicleDetailScreenState extends State<VehicleDetailScreen> {
  DateTime? _pickupDate;
  DateTime? _returnDate;
  final double _depositAmount = 3000.0;

  // Smart Pricing State
  bool _isLoadingPricing = false;
  Map<String, dynamic>? _pricingDetails;

  @override
  void initState() {
    super.initState();
    _pickupDate = widget.initialPickupDate;
    _returnDate = widget.initialReturnDate;
    
    if (_pickupDate != null && _returnDate != null) {
      _fetchPricing();
    }
  }

  int get _rentalDays {
    if (_pickupDate == null || _returnDate == null) return 0;
    return _returnDate!.difference(_pickupDate!).inDays + 1;
  }

  double get _totalPrice => _rentalDays * widget.vehicle.pricePerDay;

  DateTime? get _turnaroundDate {
    if (_returnDate == null) return null;
    return _returnDate!.add(const Duration(days: 1));
  }

  Future<void> _selectDates() async {
    final pickedRange = await showDateRangePicker(
      context: context,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
      initialDateRange: _pickupDate != null && _returnDate != null
          ? DateTimeRange(start: _pickupDate!, end: _returnDate!)
          : null,
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: AppTheme.orange,
              onPrimary: Colors.white,
              surface: AppTheme.dark,
              onSurface: AppTheme.text,
            ),
          ),
          child: child!,
        );
      },
    );

    if (pickedRange != null) {
      setState(() {
        _pickupDate = pickedRange.start;
        _returnDate = pickedRange.end;
        _pricingDetails = null; // Clear old pricing
      });
      _fetchPricing();
    }
  }

  Future<void> _fetchPricing() async {
    if (_pickupDate == null || _returnDate == null) return;
    
    setState(() => _isLoadingPricing = true);
    
    final pickupStr = "${_pickupDate!.year}-${_pickupDate!.month.toString().padLeft(2, '0')}-${_pickupDate!.day.toString().padLeft(2, '0')}";
    final returnStr = "${_returnDate!.year}-${_returnDate!.month.toString().padLeft(2, '0')}-${_returnDate!.day.toString().padLeft(2, '0')}";
    
    final result = await ApiService.fetchPricingPreview(widget.vehicle.id, pickupStr, returnStr);
    
    if (mounted) {
      setState(() {
        _pricingDetails = result;
        _isLoadingPricing = false;
      });
    }
  }

  void _confirmBooking() async {
    if (sessionUser.kycStatus == 'unverified') {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Driver KYC Profile required. Please verify your driver license first!'),
          backgroundColor: AppTheme.red,
        ),
      );
      return;
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator(color: AppTheme.orange)),
    );

    final pickupStr = "${_pickupDate!.year}-${_pickupDate!.month.toString().padLeft(2, '0')}-${_pickupDate!.day.toString().padLeft(2, '0')}";
    final returnStr = "${_returnDate!.year}-${_returnDate!.month.toString().padLeft(2, '0')}-${_returnDate!.day.toString().padLeft(2, '0')}";

    final result = await ApiService.createBooking(widget.vehicle.id, pickupStr, returnStr);

    if (!mounted) return;
    Navigator.pop(context); // pop progress indicator

    if (result != null && result['success'] == true) {
      final Booking newBooking = result['booking'];
      setState(() {
        userBookings.insert(0, newBooking);
      });

      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('🎉 Reservation Placed!'),
          content: const Text(
            'Your car booking is submitted. Next, please upload your GCash payment reference or complete verification on your rentals dashboard!',
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context); // pop dialog
                Navigator.pop(context); // pop detail screen
              },
              child: const Text('View My Rentals', style: TextStyle(color: AppTheme.orange)),
            ),
          ],
        ),
      );
    } else {
      final errorMsg = result?['message'] ?? 'Failed to place reservation.';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('❌ $errorMsg'),
          backgroundColor: AppTheme.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 280,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: Image.network(
                widget.vehicle.imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) =>
                    Container(color: AppTheme.darkCard, child: const Icon(Icons.image_not_supported, size: 64)),
              ),
            ),
          ),

          SliverFillRemaining(
            hasScrollBody: false,
            child: Padding(
              padding: const EdgeInsets.all(20.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(widget.vehicle.name, style: Theme.of(context).textTheme.headlineMedium),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(color: AppTheme.orange.withValues(alpha: 0.12), borderRadius: BorderRadius.circular(8)),
                        child: Text(
                          '₱${widget.vehicle.pricePerDay.toStringAsFixed(0)}/day',
                          style: const TextStyle(color: AppTheme.orangeLight, fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      )
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('${widget.vehicle.brand} · ${widget.vehicle.type}', style: const TextStyle(color: AppTheme.muted)),
                  const Divider(height: 32, color: AppTheme.line),

                  // Specs Grid
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _buildSpecIcon(Icons.airline_seat_recline_normal, '${widget.vehicle.capacity} Seats'),
                      _buildSpecIcon(Icons.settings, widget.vehicle.transmission),
                      _buildSpecIcon(Icons.local_gas_station, widget.vehicle.fuel),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Date Picker Box
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppTheme.darkCard,
                      border: Border.all(color: _pickupDate != null ? AppTheme.orange.withValues(alpha: 0.3) : AppTheme.line),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.date_range, color: AppTheme.orange, size: 18),
                            SizedBox(width: 8),
                            Text('SELECT RENTAL DATES', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
                          ],
                        ),
                        const SizedBox(height: 14),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('PICKUP', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
                                const SizedBox(height: 4),
                                Text(
                                  _pickupDate != null ? '${_pickupDate!.month}/${_pickupDate!.day}/${_pickupDate!.year}' : 'Select Date',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                            const Icon(Icons.arrow_forward, color: AppTheme.muted, size: 16),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('RETURN', style: TextStyle(fontSize: 10, color: AppTheme.muted)),
                                const SizedBox(height: 4),
                                Text(
                                  _returnDate != null ? '${_returnDate!.month}/${_returnDate!.day}/${_returnDate!.year}' : 'Select Date',
                                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _selectDates,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.darkCard,
                            side: const BorderSide(color: AppTheme.line),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Text('Change Dates', style: TextStyle(color: AppTheme.text, fontSize: 13)),
                        ),
                      ],
                    ),
                  ),

                  // Preventive Maintenance Notice
                  if (_returnDate != null) ...[
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppTheme.orange.withValues(alpha: 0.06),
                        border: Border.all(color: AppTheme.orange.withValues(alpha: 0.18)),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.build_circle_outlined, color: AppTheme.orange, size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '🔧 Turnaround Date: ${_turnaroundDate!.month}/${_turnaroundDate!.day}/${_turnaroundDate!.year}',
                                  style: const TextStyle(color: AppTheme.orangeLight, fontWeight: FontWeight.bold, fontSize: 13),
                                ),
                                const SizedBox(height: 2),
                                const Text(
                                  'This car will be locked out on this day for deep cleaning and preventative safety inspection.',
                                  style: TextStyle(color: AppTheme.muted, fontSize: 11),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const SizedBox(height: 24),

                  // Price Summary Details
                  if (_rentalDays > 0) ...[
                    const Text('PRICE BREAKDOWN', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.textDim)),
                    const SizedBox(height: 8),
                    
                    if (_isLoadingPricing)
                      const Center(child: Padding(padding: EdgeInsets.all(16.0), child: CircularProgressIndicator(color: AppTheme.orange, strokeWidth: 2)))
                    else if (_pricingDetails != null) ...[
                       _buildPriceRow('Daily Rate', '₱${widget.vehicle.pricePerDay.toStringAsFixed(0)}'),
                       _buildPriceRow('Duration', '$_rentalDays days'),
                       _buildPriceRow('Base Subtotal', '₱${_pricingDetails!['base_price'].toStringAsFixed(2)}'),
                       
                       Builder(builder: (context) {
                         double combined = _pricingDetails!['combined_multiplier'] ?? 
                                         (_pricingDetails!['demand_multiplier'] * _pricingDetails!['timeline_multiplier'] * _pricingDetails!['availability_multiplier']);
                         
                         if (combined > 1.01) {
                           double extra = _pricingDetails!['final_price'] - _pricingDetails!['base_price'];
                           return _buildPriceRow('Dynamic Demand Adjustment', '+ ₱${extra.toStringAsFixed(2)}', valueColor: AppTheme.red);
                         } else if (combined < 0.99) {
                           double discount = _pricingDetails!['base_price'] - _pricingDetails!['final_price'];
                           return _buildPriceRow('Early Bird Discount', '- ₱${discount.toStringAsFixed(2)}', valueColor: Colors.green);
                         }
                         return const SizedBox.shrink();
                       }),
                       
                       const SizedBox(height: 12),
                       _buildPriceRow('Smart Total', '₱${_pricingDetails!['final_price'].toStringAsFixed(2)}', isBold: true, valueColor: AppTheme.orangeLight),
                    ] else ...[
                       // Fallback if API fails
                       _buildPriceRow('Daily Rate', '₱${widget.vehicle.pricePerDay.toStringAsFixed(2)}'),
                       _buildPriceRow('Duration', '$_rentalDays days'),
                       _buildPriceRow('Rental Rate (${_rentalDays} days)', '₱${_totalPrice.toStringAsFixed(2)}', isBold: true),
                    ],
                    
                    const Divider(color: AppTheme.line, height: 24),
                    _buildPriceRow('Refundable Security Deposit', '₱${_depositAmount.toStringAsFixed(2)}'),
                    const Divider(color: AppTheme.line, height: 16),
                    _buildPriceRow('Estimated Total to Pay', '₱${((_pricingDetails != null ? _pricingDetails!['final_price'] : _totalPrice) + _depositAmount).toStringAsFixed(2)}', isBold: true, valueColor: AppTheme.orangeLight),
                    
                    if (_pricingDetails != null) ...[
                      Builder(builder: (context) {
                        double combined = _pricingDetails!['combined_multiplier'] ?? 
                                        (_pricingDetails!['demand_multiplier'] * _pricingDetails!['timeline_multiplier'] * _pricingDetails!['availability_multiplier']);
                        if (combined < 0.99) {
                           return const Padding(
                            padding: EdgeInsets.only(top: 8.0),
                            child: Text('🎉 Includes Early Bird Discount!', style: TextStyle(color: Colors.green, fontSize: 12, fontWeight: FontWeight.bold), textAlign: TextAlign.right),
                          );
                        }
                        return const SizedBox.shrink();
                      }),
                    ],
                    const SizedBox(height: 24),
                  ],

                  ElevatedButton(
                    onPressed: _rentalDays > 0 ? _confirmBooking : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.orange,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      disabledBackgroundColor: AppTheme.darkCard,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: const Text('Book Reservation Now', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          )
        ],
      ),
    );
  }

  Widget _buildSpecIcon(IconData icon, String text) {
    return Container(
      width: 100,
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(color: AppTheme.darkCard, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppTheme.line)),
      child: Column(
        children: [
          Icon(icon, color: AppTheme.orangeLight, size: 24),
          const SizedBox(height: 6),
          Text(text, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }

  Widget _buildPriceRow(String label, String value, {bool isBold = false, Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: 14, color: isBold ? AppTheme.text : AppTheme.muted, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(value, style: TextStyle(fontSize: 14, color: valueColor ?? (isBold ? AppTheme.text : AppTheme.text), fontWeight: isBold ? FontWeight.w900 : FontWeight.normal)),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 5. RENTALS DASHBOARD & TRACKING MILESTONES SCREEN
// ─────────────────────────────────────────────────────────────────────────────
