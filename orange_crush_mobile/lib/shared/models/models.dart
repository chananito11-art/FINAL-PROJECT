class Vehicle {
  final int id;
  final String name;
  final String brand;
  final String type;
  final String transmission;
  final String fuel;
  final int capacity;
  final double pricePerDay;
  final String imageUrl;
  final int odometer;
  final String status;

  Vehicle({
    required this.id,
    required this.name,
    required this.brand,
    required this.type,
    required this.transmission,
    required this.fuel,
    required this.capacity,
    required this.pricePerDay,
    required this.imageUrl,
    required this.odometer,
    required this.status,
  });
}

class Booking {
  final int id;
  final Vehicle vehicle;
  final DateTime pickupDate;
  final DateTime returnDate;
  final double totalAmount;
  final double paidAmount;
  final double balanceAmount;
  final double securityDeposit;
  final String securityDepositStatus;
  String status; // e.g., 'pending_payment', 'awaiting_verification', 'confirmed', 'ongoing', 'completed'
  final List<PaymentRecord> payments;
  final List<InspectionRecord> inspections;

  Booking({
    required this.id,
    required this.vehicle,
    required this.pickupDate,
    required this.returnDate,
    required this.totalAmount,
    required this.paidAmount,
    required this.balanceAmount,
    required this.securityDeposit,
    required this.securityDepositStatus,
    required this.status,
    required this.payments,
    required this.inspections,
  });

  double get outstandingBalance => totalAmount - paidAmount;
}

class PaymentRecord {
  final String date;
  final String method;
  final double amount;
  final String status;
  final String notes;

  PaymentRecord({
    required this.date,
    required this.method,
    required this.amount,
    required this.status,
    required this.notes,
  });
}

class InspectionRecord {
  final String type; // 'pickup' or 'return'
  final String date;
  final int odometer;
  final int fuel;
  final String notes;

  InspectionRecord({
    required this.type,
    required this.date,
    required this.odometer,
    required this.fuel,
    required this.notes,
  });
}

// ── MOCK FLEET DATA ──
final List<Vehicle> mockVehicles = [
  Vehicle(
    id: 1,
    name: 'Toyota Vios',
    brand: 'Toyota',
    type: 'Sedan',
    transmission: 'Automatic',
    fuel: 'Gasoline',
    capacity: 5,
    pricePerDay: 1500.0,
    imageUrl: 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80',
    odometer: 12000,
    status: 'available',
  ),
  Vehicle(
    id: 2,
    name: 'Honda HR-V',
    brand: 'Honda',
    type: 'Crossover',
    transmission: 'Automatic',
    fuel: 'Gasoline',
    capacity: 5,
    pricePerDay: 2200.0,
    imageUrl: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
    odometer: 8500,
    status: 'available',
  ),
  Vehicle(
    id: 3,
    name: 'Mitsubishi Montero Sport',
    brand: 'Mitsubishi',
    type: 'SUV',
    transmission: 'Automatic',
    fuel: 'Diesel',
    capacity: 7,
    pricePerDay: 3500.0,
    imageUrl: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=600&q=80',
    odometer: 24000,
    status: 'available',
  ),
  Vehicle(
    id: 4,
    name: 'Ford Ranger',
    brand: 'Ford',
    type: 'Pickup Truck',
    transmission: 'Automatic',
    fuel: 'Diesel',
    capacity: 5,
    pricePerDay: 3000.0,
    imageUrl: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=600&q=80',
    odometer: 15400,
    status: 'available',
  ),
  Vehicle(
    id: 5,
    name: 'Toyota Innova',
    brand: 'Toyota',
    type: 'MPV',
    transmission: 'Automatic',
    fuel: 'Diesel',
    capacity: 8,
    pricePerDay: 2800.0,
    imageUrl: 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80',
    odometer: 32000,
    status: 'available',
  ),
];
