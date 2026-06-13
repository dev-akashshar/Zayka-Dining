<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mail = new App\Mail\OtpMail('123456');
$ref = new ReflectionClass($mail);
echo "Parent class: " . $ref->getParentClass()->getName() . "\n";
echo "Has getEnvelope: " . ($ref->hasMethod('getEnvelope') ? 'Yes' : 'No') . "\n";
echo "Has getContent: " . ($ref->hasMethod('getContent') ? 'Yes' : 'No') . "\n";

// Let's call the methods that construct the views in Laravel
if ($ref->hasMethod('buildView')) {
    $method = $ref->getMethod('buildView');
    $method->setAccessible(true);
    try {
        $viewData = $method->invoke($mail);
        echo "buildView output:\n";
        print_r($viewData);
    } catch (\Exception $e) {
        echo "buildView failed: " . $e->getMessage() . "\n";
    }
}
