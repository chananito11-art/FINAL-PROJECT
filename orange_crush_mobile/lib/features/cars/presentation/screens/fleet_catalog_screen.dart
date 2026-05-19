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

class FleetCatalogScreen extends StatefulWidget {
  const FleetCatalogScreen({super.key});

  @override
  State<FleetCatalogScreen> createState() => _FleetCatalogScreenState();
}

class _FleetCatalogScreenState extends State<FleetCatalogScreen> {
  List<Vehicle> _vehicles = [];
  bool _isLoading = true;

  // New Date & Filter States matching the Web Client Business Process
  DateTime? _pickupDate;
  DateTime? _returnDate;
  String _selectedType = 'All';
  String _selectedCapacity = 'Any';

  final List<String> _filters = ['All', 'Sedan', 'SUV', 'Crossover', 'Pickup Truck', 'MPV'];
  final List<String> _capacities = ['Any', '2', '4', '5', '7', '8'];

  @override
  void initState() {
    super.initState();
    _loadVehicles();
  }

  Future<void> _loadVehicles() async {
    setState(() => _isLoading = true);
    int? cap;
    if (_selectedCapacity != 'Any') {
      cap = int.tryParse(_selectedCapacity);
    }

    final list = await ApiService.fetchVehicles(
      pickupDate: _pickupDate != null ? _formatDate(_pickupDate) : null,
      returnDate: _returnDate != null ? _formatDate(_returnDate) : null,
      type: _selectedType != 'All' ? _selectedType : null,
      capacity: cap,
    );

    if (mounted) {
      setState(() {
        _vehicles = list;
        _isLoading = false;
      });
    }
  }

  String _formatDate(DateTime? date) {
    if (date == null) return 'Select Date';
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  Future<void> _selectDate(BuildContext context, bool isPickup) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: isPickup 
          ? (_pickupDate ?? DateTime.now()) 
          : (_returnDate ?? (_pickupDate ?? DateTime.now()).add(const Duration(days: 1))),
      firstDate: isPickup ? DateTime.now() : (_pickupDate ?? DateTime.now()),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: ThemeData.dark().copyWith(
            colorScheme: const ColorScheme.dark(
              primary: AppTheme.orange,
              onPrimary: Colors.white,
              surface: AppTheme.darkCard,
              onSurface: AppTheme.text,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        if (isPickup) {
          _pickupDate = picked;
          if (_returnDate != null && _returnDate!.isBefore(_pickupDate!)) {
            _returnDate = _pickupDate!.add(const Duration(days: 1));
          }
        } else {
          _returnDate = picked;
        }
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.darkBg,
      appBar: AppBar(
        title: const Text('OrangeCrush Fleet', style: TextStyle(fontWeight: FontWeight.w900)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadVehicles,
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadVehicles,
        color: AppTheme.orange,
        child: ListView(
          padding: const EdgeInsets.symmetric(vertical: 8),
          children: [
            // ── WEBSPARK DATE AVAILABILITY PANEL ──
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.darkCard,
                border: Border.all(color: AppTheme.line),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Top alert banner
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: Colors.orange.withOpacity(0.08),
                      border: Border.all(color: Colors.orange.withOpacity(0.15)),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.calendar_month_rounded, color: AppTheme.orangeLight, size: 24),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Select your rental dates first',
                                style: TextStyle(
                                  color: AppTheme.orangeLight,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                              const SizedBox(height: 2),
                              const Text(
                                'Pick a pickup and return date to see only available vehicles for your trip.',
                                style: TextStyle(
                                  color: AppTheme.muted,
                                  fontSize: 10.5,
                                  height: 1.2,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Grid Fields Row 1 (Pickup & Return Dates)
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'PICKUP DATE',
                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.muted),
                            ),
                            const SizedBox(height: 6),
                            InkWell(
                              onTap: () => _selectDate(context, true),
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppTheme.line),
                                  borderRadius: BorderRadius.circular(12),
                                  color: AppTheme.darkBg,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      _pickupDate != null ? _formatDate(_pickupDate) : 'Select Date',
                                      style: TextStyle(
                                        color: _pickupDate != null ? AppTheme.text : AppTheme.muted,
                                        fontSize: 13,
                                        fontWeight: _pickupDate != null ? FontWeight.bold : FontWeight.normal,
                                      ),
                                    ),
                                    const Icon(Icons.calendar_today, size: 14, color: AppTheme.muted),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'RETURN DATE',
                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.muted),
                            ),
                            const SizedBox(height: 6),
                            InkWell(
                              onTap: () => _selectDate(context, false),
                              borderRadius: BorderRadius.circular(12),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppTheme.line),
                                  borderRadius: BorderRadius.circular(12),
                                  color: AppTheme.darkBg,
                                ),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      _returnDate != null ? _formatDate(_returnDate) : 'Select Date',
                                      style: TextStyle(
                                        color: _returnDate != null ? AppTheme.text : AppTheme.muted,
                                        fontSize: 13,
                                        fontWeight: _returnDate != null ? FontWeight.bold : FontWeight.normal,
                                      ),
                                    ),
                                    const Icon(Icons.calendar_today, size: 14, color: AppTheme.muted),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Grid Fields Row 2 (Vehicle Type & Min Capacity Dropdowns)
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'VEHICLE TYPE',
                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.muted),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              decoration: BoxDecoration(
                                border: Border.all(color: AppTheme.line),
                                borderRadius: BorderRadius.circular(12),
                                color: AppTheme.darkBg,
                              ),
                              child: DropdownButtonHideUnderline(
                                child: DropdownButton<String>(
                                  value: _selectedType,
                                  isExpanded: true,
                                  dropdownColor: AppTheme.darkCard,
                                  icon: const Icon(Icons.keyboard_arrow_down, color: AppTheme.muted, size: 18),
                                  items: _filters.map((type) {
                                    return DropdownMenuItem<String>(
                                      value: type,
                                      child: Text(type == 'All' ? 'All Types' : type, style: const TextStyle(fontSize: 13, color: AppTheme.text)),
                                    );
                                  }).toList(),
                                  onChanged: (val) {
                                    if (val != null) {
                                      setState(() => _selectedType = val);
                                    }
                                  },
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'MIN CAPACITY',
                              style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.muted),
                            ),
                            const SizedBox(height: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              decoration: BoxDecoration(
                                border: Border.all(color: AppTheme.line),
                                borderRadius: BorderRadius.circular(12),
                                color: AppTheme.darkBg,
                              ),
                              child: DropdownButtonHideUnderline(
                                child: DropdownButton<String>(
                                  value: _selectedCapacity,
                                  isExpanded: true,
                                  dropdownColor: AppTheme.darkCard,
                                  icon: const Icon(Icons.keyboard_arrow_down, color: AppTheme.muted, size: 18),
                                  items: _capacities.map((cap) {
                                    return DropdownMenuItem<String>(
                                      value: cap,
                                      child: Text(cap == 'Any' ? 'Any Capacity' : '$cap Seats', style: const TextStyle(fontSize: 13, color: AppTheme.text)),
                                    );
                                  }).toList(),
                                  onChanged: (val) {
                                    if (val != null) {
                                      setState(() => _selectedCapacity = val);
                                    }
                                  },
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Show Available Cars Action Button
                  ElevatedButton(
                    onPressed: _loadVehicles,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.orange,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 0,
                    ),
                    child: const Text(
                      'Show Available Cars',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 4),
              child: Text(
                _pickupDate != null && _returnDate != null
                    ? 'AVAILABLE VEHICLES (${_vehicles.length})'
                    : 'ALL VEHICLES (${_vehicles.length})',
                style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.muted, letterSpacing: 1),
              ),
            ),
            const SizedBox(height: 8),

            if (_isLoading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 48.0),
                child: Center(child: CircularProgressIndicator(color: AppTheme.orange)),
              )
            else if (_vehicles.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 48.0),
                child: Center(child: Text('No vehicles available for these options.', style: TextStyle(color: AppTheme.muted))),
              )
            else
              ..._vehicles.map((vehicle) {
                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Card(
                    margin: EdgeInsets.zero,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(16),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => VehicleDetailScreen(
                              vehicle: vehicle,
                              initialPickupDate: _pickupDate,
                              initialReturnDate: _returnDate,
                            ),
                          ),
                        );
                      },
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Hero image
                          ClipRRect(
                            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                            child: Image.network(
                              vehicle.imageUrl,
                              height: 180,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) =>
                                  Container(color: AppTheme.darkCard, height: 180, child: const Icon(Icons.image_not_supported)),
                            ),
                          ),
                          Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      vehicle.name,
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      '${vehicle.type} • ${vehicle.capacity} Seats',
                                      style: const TextStyle(color: AppTheme.muted, fontSize: 12),
                                    ),
                                  ],
                                ),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    const Text(
                                      'Starts at',
                                      style: TextStyle(color: AppTheme.orangeLight, fontWeight: FontWeight.bold, fontSize: 10),
                                    ),
                                    Text(
                                      '₱${vehicle.pricePerDay.toStringAsFixed(0)}',
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: AppTheme.text),
                                    ),
                                    const Text(
                                      '/ day',
                                      style: TextStyle(color: AppTheme.muted, fontSize: 10),
                                    ),
                                  ],
                                )
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 📱 4. VEHICLE DETAIL & BUFFER DAY BOOKING SCREEN
// ─────────────────────────────────────────────────────────────────────────────
