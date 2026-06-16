<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Mail\OtpMail;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;

try {
    Mail::to('test@example.com')->send(new OtpMail('123456'));
    echo "Mail sent successfully via Log driver!\n";
} catch (Exception $e) {
    echo 'Failed: '.$e->getMessage()."\n";
    echo $e->getTraceAsString()."\n";
}
