<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::to('test@example.com')->send(new App\Mail\OtpMail('123456'));
    echo "Mail sent successfully via Log driver!\n";
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
