<?php

use App\Models\Otp;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$otp = Otp::where('email', 'newuser123@example.com')->latest()->first();
if ($otp) {
    echo 'OTP:'.$otp->code."\n";
} else {
    echo "No OTP found\n";
}
