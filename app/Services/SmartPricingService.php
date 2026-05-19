<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\Booking;

class SmartPricingService
{
    /**
     * Calculate the final price based on smart pricing factors.
     * 
     * Formula: Final price = base_price * demand_multiplier * timeline_multiplier * availability_multiplier
     * 
     * @param Vehicle $vehicle
     * @param Carbon $pickupDate
     * @param Carbon $returnDate
     * @return float
     */
    public function calculateFinalPrice(Vehicle $vehicle, Carbon $pickupDate, Carbon $returnDate): float
    {
        $days = $pickupDate->diffInDays($returnDate) ?: 1;
        $basePrice = $vehicle->price_per_day * $days;

        $demandMultiplier = $this->getDemandMultiplier($pickupDate, $returnDate);
        $timelineMultiplier = $this->getTimelineMultiplier($pickupDate);
        $availabilityMultiplier = $this->getAvailabilityMultiplier($vehicle, $pickupDate, $returnDate);
        
        $combinedMultiplier = $demandMultiplier * $timelineMultiplier * $availabilityMultiplier;
        
        $finalPrice = $basePrice * $combinedMultiplier;

        return round($finalPrice, 2);
    }

    /**
     * Get details of the calculation for UI/Debugging.
     */
    public function getPricingDetails(Vehicle $vehicle, Carbon $pickupDate, Carbon $returnDate): array
    {
        $days = $pickupDate->diffInDays($returnDate) ?: 1;
        $basePrice = $vehicle->price_per_day * $days;

        $demandMultiplier = $this->getDemandMultiplier($pickupDate, $returnDate);
        $timelineMultiplier = $this->getTimelineMultiplier($pickupDate);
        $availabilityMultiplier = $this->getAvailabilityMultiplier($vehicle, $pickupDate, $returnDate);
        
        $combinedMultiplier = $demandMultiplier * $timelineMultiplier * $availabilityMultiplier;
        
        $finalPrice = round($basePrice * $combinedMultiplier, 2);

        return [
            'base_price' => $basePrice,
            'demand_multiplier' => $demandMultiplier,
            'timeline_multiplier' => $timelineMultiplier,
            'availability_multiplier' => $availabilityMultiplier,
            'combined_multiplier' => $combinedMultiplier,
            'final_price' => $finalPrice,
            'days' => $days
        ];
    }

    /**
     * Demand Multiplier based on Philippine calendar and seasons.
     */
    private function getDemandMultiplier(Carbon $pickupDate, Carbon $returnDate): float
    {
        $multiplier = 1.0;

        // Peak seasons in PH:
        $month = $pickupDate->month;
        if (in_array($month, [3, 4, 5])) {
            $multiplier += 0.04; // 4% increase during summer
        } elseif ($month === 12) {
            $multiplier += 0.05; // 5% increase during Christmas season
        }

        // Philippine Fixed Holidays
        $holidays = [
            '01-01', // New Year's Day
            '02-25', // EDSA Revolution Anniversary
            '04-09', // Araw ng Kagitingan
            '05-01', // Labor Day
            '06-12', // Independence Day
            '08-21', // Ninoy Aquino Day
            '08-26', // National Heroes Day (approx)
            '11-01', // All Saints' Day
            '11-02', // All Souls' Day
            '11-30', // Bonifacio Day
            '12-08', // Feast of the Immaculate Conception
            '12-24', // Christmas Eve
            '12-25', // Christmas Day
            '12-30', // Rizal Day
            '12-31', // New Year's Eve
        ];

        // Compute Holy Week dates (Maundy Thursday, Good Friday, Black Saturday)
        $year = $pickupDate->year;
        $easterTimestamp = easter_date($year);
        $easter = Carbon::createFromTimestamp($easterTimestamp);
        $holyWeekDates = [
            $easter->copy()->subDays(3)->format('m-d'), // Maundy Thursday
            $easter->copy()->subDays(2)->format('m-d'), // Good Friday
            $easter->copy()->subDays(1)->format('m-d'), // Black Saturday
        ];
        $holidays = array_merge($holidays, $holyWeekDates);

        $currentDate = $pickupDate->copy();
        $holidayFound = false;
        $weekendFound = false;

        while ($currentDate->lte($returnDate)) {
            $dateString = $currentDate->format('m-d');

            if (in_array($dateString, $holidays)) {
                $holidayFound = true;
            }

            if ($currentDate->isWeekend()) {
                $weekendFound = true;
            }

            // Early exit if both found
            if ($holidayFound && $weekendFound) break;

            $currentDate->addDay();
        }

        if ($holidayFound) {
            $multiplier += 0.05; // 5% increase when rental spans a holiday
        }
        
        if ($weekendFound) {
            $multiplier += 0.02; // 2% increase for weekend bookings
        }

        return $multiplier;
    }

    /**
     * Timeline Multiplier based on booking lead time.
     */
    private function getTimelineMultiplier(Carbon $pickupDate): float
    {
        $leadTimeDays = now()->diffInDays($pickupDate, false);

        if ($leadTimeDays < 0) {
            $leadTimeDays = 0; // Prevent negative
        }

        if ($leadTimeDays <= 3) {
            // Last minute booking
            return 1.03; // 3% increase
        } elseif ($leadTimeDays >= 30) {
            // Early bird booking
            return 0.95; // 5% discount
        }

        return 1.0; // Normal rate
    }

    /**
     * Availability Multiplier based on fleet utilization.
     */
    private function getAvailabilityMultiplier(Vehicle $vehicle, Carbon $pickupDate, Carbon $returnDate): float
    {
        // Get all vehicles of the same type
        $totalVehiclesOfType = Vehicle::where('type', $vehicle->type)->count();
        
        if ($totalVehiclesOfType === 0) {
            return 1.0;
        }

        // Count how many are available for this specific date range
        $availableVehicles = 0;
        $vehiclesOfType = Vehicle::where('type', $vehicle->type)->get();
        
        foreach ($vehiclesOfType as $v) {
            if (Vehicle::isAvailableForDates($v->id, $pickupDate, $returnDate)) {
                $availableVehicles++;
            }
        }

        $availabilityPercentage = $availableVehicles / $totalVehiclesOfType;

        if ($availabilityPercentage <= 0.20) {
            // High demand, low availability (< 20%)
            return 1.05; // 5% increase
        } elseif ($availabilityPercentage <= 0.50) {
            // Medium availability (< 50%)
            return 1.03; // 3% increase
        }

        return 1.0;
    }
}
