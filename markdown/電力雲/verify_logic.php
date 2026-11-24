<?php

// Replicating the logic from CalculateTaiwanPowerBillCommand::handle for verification

// Inputs from Image Example
$c_normal = 500;
$d_normal = 500; // Assume demand = contract (no penalty)
// Other demands 0 or logic ensures no penalty if under contract. 
// Image says "Highest demand did not exceed contract"
$baseRate = 223.60; // Explicitly using Image's Base Rate (Code has 209.87)

$kwh_peak = 10000;
$kwh_half = 15000;
$kwh_sat = 5000;
$kwh_off = 20000;

$rates = [
    'peak' => 9.39,
    'half' => 5.85,
    'sat' => 2.60,
    'off' => 2.53
];

// Logic Step 1: Base Fee
$agreedBase = round($c_normal * $baseRate, 1);

// Logic Step 2: Flow Fee
$flowFee = round(
    ($kwh_peak * $rates['peak']) +
    ($kwh_half * $rates['half']) +
    ($kwh_sat  * $rates['sat']) +
    ($kwh_off  * $rates['off']),
    1
);

// Logic Step 3: Total (Ignoring PF Bonus/Tax for this basic Logic Check as user requested)
$total = $agreedBase + $flowFee;

echo "Agreed Base: " . number_format($agreedBase, 1) . "\n";
echo "Flow Fee: " . number_format($flowFee, 1) . "\n";
echo "Total: " . number_format($total, 0) . "\n";

