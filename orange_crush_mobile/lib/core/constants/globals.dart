import 'package:orange_crush_mobile/shared/models/models.dart';

// ── CUSTOMER PROFILE STATE ──
class UserProfile {
  String firstName = '';
  String lastName = '';
  String email = '';
  String phone = '';
  String licenseNumber = '';
  String kycStatus = 'unverified'; // unverified, pending, verified
}

// ── GLOBAL STATE ──
UserProfile sessionUser = UserProfile();
List<Booking> userBookings = [];

