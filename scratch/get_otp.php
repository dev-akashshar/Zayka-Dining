<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$otp = App\Models\Otp::where('email', 'newuser123@example.com')->latest()->first();
if ($otp) {
    echo "OTP:" . $otp->code . "\n";
} else {
    echo "No OTP found\n";
}
