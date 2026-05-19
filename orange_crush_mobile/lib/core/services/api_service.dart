import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:orange_crush_mobile/shared/models/models.dart';

class ApiService {
  // Use http://10.0.2.2:8000/api for Android Emulator, http://127.0.0.1:8000/api for iOS/Web/Desktop
  static String baseUrl = 'http://192.168.1.42:8000/api';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('access_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('access_token', token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('access_token');
  }

  static Future<Map<String, String>> _getHeaders() async {
    final token = await getToken();
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // ── Authentication ──
  static Future<bool> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'email': email,
          'password': password,
          'device_name': Platform.isAndroid ? 'Android Emulator' : 'Mobile Client',
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        await saveToken(data['access_token']);
        return true;
      }
      return false;
    } catch (e) {
      print('API Error login: $e');
      return false;
    }
  }

  static Future<void> logout() async {
    try {
      final headers = await _getHeaders();
      await http.post(Uri.parse('$baseUrl/logout'), headers: headers);
    } catch (e) {
      print('API Error logout: $e');
    } finally {
      await clearToken();
    }
  }

  // ── User Profile ──
  static Future<Map<String, dynamic>?> fetchProfile() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(Uri.parse('$baseUrl/user'), headers: headers);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      print('API Error fetchProfile: $e');
      return null;
    }
  }

  static Future<bool> submitVerification(String licenseNumber, String expirationDate, String filePath) async {
    try {
      final token = await getToken();
      final uri = Uri.parse('$baseUrl/user/verify');
      final request = http.MultipartRequest('POST', uri)
        ..headers.addAll({
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        })
        ..fields['license_number'] = licenseNumber
        ..fields['expiration_date'] = expirationDate
        ..files.add(await http.MultipartFile.fromPath('file', filePath));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        return true;
      }
      print('API Error verification upload status: ${response.statusCode}, body: ${response.body}');
      return false;
    } catch (e) {
      print('API Error submitVerification: $e');
      return false;
    }
  }

  // ── Fleet Catalog ──
  static Future<List<Vehicle>> fetchVehicles({
    String? pickupDate,
    String? returnDate,
    String? type,
    int? capacity,
  }) async {
    try {
      final headers = await _getHeaders();
      String url = '$baseUrl/vehicles';
      final List<String> queryParams = [];
      if (pickupDate != null) queryParams.add('pickup_date=$pickupDate');
      if (returnDate != null) queryParams.add('return_date=$returnDate');
      if (type != null && type != 'All') queryParams.add('type=$type');
      if (capacity != null) queryParams.add('capacity=$capacity');

      if (queryParams.isNotEmpty) {
        url += '?${queryParams.join('&')}';
      }

      final response = await http.get(Uri.parse(url), headers: headers);

      if (response.statusCode == 200) {
        final List<dynamic> data = jsonDecode(response.body);
        return data.map((item) => _parseVehicle(item)).toList();
      }
      return [];
    } catch (e) {
      print('API Error fetchVehicles: $e');
      return [];
    }
  }

  // ── Bookings ──
  static Future<List<Booking>> fetchBookings() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(Uri.parse('$baseUrl/bookings'), headers: headers);

      if (response.statusCode == 200) {
        final List<dynamic> data = jsonDecode(response.body);
        return data.map((item) => _parseBooking(item)).toList();
      }
      return [];
    } catch (e) {
      print('API Error fetchBookings: $e');
      return [];
    }
  }

  static Future<Map<String, dynamic>?> createBooking(int vehicleId, String pickupDate, String returnDate) async {
    try {
      final headers = await _getHeaders();
      final response = await http.post(
        Uri.parse('$baseUrl/bookings'),
        headers: headers,
        body: jsonEncode({
          'vehicle_id': vehicleId,
          'pickup_date': pickupDate,
          'return_date': returnDate,
        }),
      );

      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        return {'success': true, 'booking': _parseBooking(data['booking'])};
      }
      return {'success': false, 'message': data['message'] ?? 'Failed to place booking.'};
    } catch (e) {
      print('API Error createBooking: $e');
      return {'success': false, 'message': 'Connection error occurred.'};
    }
  }

  static Future<Map<String, dynamic>?> submitPayment(int bookingId, double amount, String refNumber, String accountName, String filePath) async {
    try {
      final token = await getToken();
      final uri = Uri.parse('$baseUrl/bookings/$bookingId/pay');
      
      final request = http.MultipartRequest('POST', uri)
        ..headers.addAll({
          'Accept': 'application/json',
          if (token != null) 'Authorization': 'Bearer $token',
        })
        ..fields['amount_submitted'] = amount.toString()
        ..fields['gcash_transaction_reference_number'] = refNumber
        ..fields['gcash_account_name'] = accountName
        ..files.add(await http.MultipartFile.fromPath('screenshot', filePath));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      
      print('submitPayment status: ${response.statusCode}');
      print('submitPayment body: ${response.body}');

      Map<String, dynamic> data;
      try {
        data = jsonDecode(response.body);
      } catch (_) {
        return {
          'success': false,
          'message': 'Server error ${response.statusCode}: ${response.body.substring(0, response.body.length > 200 ? 200 : response.body.length)}'
        };
      }

      if (response.statusCode == 200) {
        return {'success': true, 'booking': _parseBooking(data['booking'])};
      }
      
      // Extract validation messages if present
      if (data['errors'] != null && data['errors'] is Map) {
        final errs = data['errors'] as Map;
        final list = errs.values.map((v) => v is List ? v.join(', ') : v.toString()).join(' | ');
        return {'success': false, 'message': list};
      }

      return {'success': false, 'message': data['message'] ?? 'Failed to submit payment.'};
    } catch (e) {
      print('API Error submitPayment: $e');
      return {'success': false, 'message': 'Connection error occurred: $e'};
    }
  }

  // ── Dashboard stats ──
  static Future<Map<String, dynamic>?> fetchDashboard() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(Uri.parse('$baseUrl/dashboard'), headers: headers);

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return {
          'stats': data['stats'],
          'recent_bookings': (data['recent_bookings'] as List<dynamic>)
              .map((item) => _parseBooking(item))
              .toList(),
          'recommended_vehicles': (data['recommended_vehicles'] as List<dynamic>)
              .map((item) => _parseVehicle(item))
              .toList(),
        };
      }
      return null;
    } catch (e) {
      print('API Error fetchDashboard: $e');
      return null;
    }
  }

  // ── Transaction logs ──
  static Future<List<Map<String, dynamic>>> fetchTransactions() async {
    try {
      final headers = await _getHeaders();
      final response = await http.get(Uri.parse('$baseUrl/transactions'), headers: headers);

      if (response.statusCode == 200) {
        final List<dynamic> data = jsonDecode(response.body);
        return data.map((item) => Map<String, dynamic>.from(item)).toList();
      }
      return [];
    } catch (e) {
      print('API Error fetchTransactions: $e');
      return [];
    }
  }

  // ── Smart Pricing Preview ──
  static Future<Map<String, dynamic>?> fetchPricingPreview(int vehicleId, String pickupDate, String returnDate) async {
    try {
      final headers = await _getHeaders();
      final url = '$baseUrl/vehicles/$vehicleId/pricing-preview?pickup_date=$pickupDate&return_date=$returnDate';
      final response = await http.get(Uri.parse(url), headers: headers);

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return null;
    } catch (e) {
      print('API Error fetchPricingPreview: $e');
      return null;
    }
  }

  // ── Helper Parsers ──
  static Vehicle _parseVehicle(Map<String, dynamic> json) {
    return Vehicle(
      id: json['id'],
      name: json['name'],
      brand: json['brand'] ?? '',
      type: json['type'] ?? 'Sedan',
      transmission: json['transmission'] ?? 'Automatic',
      fuel: json['fuel'] ?? 'Gasoline',
      capacity: json['capacity'] ?? 5,
      pricePerDay: (json['price_per_day'] as num).toDouble(),
      imageUrl: json['image_url'] ?? '',
      odometer: json['odometer'] ?? 0,
      status: json['status'] ?? 'available',
    );
  }

  static Booking _parseBooking(Map<String, dynamic> json) {
    return Booking(
      id: json['id'],
      vehicle: _parseVehicle(json['vehicle']),
      pickupDate: DateTime.parse(json['pickup_date']),
      returnDate: DateTime.parse(json['return_date']),
      totalAmount: (json['total_amount'] as num).toDouble(),
      paidAmount: (json['paid_amount'] as num).toDouble(),
      balanceAmount: (json['balance_amount'] as num).toDouble(),
      securityDeposit: (json['security_deposit'] as num).toDouble(),
      securityDepositStatus: json['security_deposit_status'] ?? 'pending',
      status: json['status'] ?? 'pending_payment',
      payments: (json['payments'] as List<dynamic>?)
              ?.map((p) => PaymentRecord(
                    date: p['date'] ?? '',
                    method: p['method'] ?? 'GCash',
                    amount: (p['amount'] as num).toDouble(),
                    status: p['status'] ?? 'pending',
                    notes: p['notes'] ?? '',
                  ))
              .toList() ??
          [],
      inspections: (json['inspections'] as List<dynamic>?)
              ?.map((i) => InspectionRecord(
                    type: i['type'] ?? 'pickup',
                    date: i['date'] ?? '',
                    odometer: i['odometer'] ?? 0,
                    fuel: i['fuel'] ?? 0,
                    notes: i['notes'] ?? '',
                  ))
              .toList() ??
          [],
    );
  }
}
