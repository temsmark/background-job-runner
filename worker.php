<?php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Import our custom classes
use App\Domain\Runner\CustomBackgroundJobRunner;
use App\Domain\CustomLogger;

// Get command line arguments
$className = $argv[1] ?? null;
$methodName = $argv[2] ?? null;
$jsonParams = $argv[3] ?? '[]';
$retryCount = $argv[4] ?? 0;

// Initialize our logger
$logger = new CustomLogger();

try {
    // Create our job runner instance
    $runner = new CustomBackgroundJobRunner($logger);

    // Execute the job through the runner
    $runner->handleCliExecution([
        $className,
        $methodName,
        $jsonParams,
        $retryCount
    ]);



} catch (Throwable $e) {
    $logger->logFailure($className ?? 'unknown', $methodName ?? 'unknown', $e);
    exit(1);
}
