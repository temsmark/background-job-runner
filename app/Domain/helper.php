<?php

use App\Domain\CustomLogger;
use App\Domain\Exceptions\JobRunnerException;
use Illuminate\Support\Facades\Log;

if (!function_exists('runBackgroundJob')) {
    /**
     * @param string $className
     * @param string $methodName
     * @param array $parameters
     * @param int $retryCount
     * @return void
     * @throws JobRunnerException
     * @throws Throwable
     */

    function runBackgroundJob(string $className, string $methodName, array $parameters = [],int $retryCount=0): void
    {

        $logger = new CustomLogger();

        try {
            $scriptPath = base_path('worker.php');

            if (!file_exists($scriptPath)) {
                throw new JobRunnerException('Worker script not found');
            }


            $logger->logSuccess($className, $methodName, 'Background job initiated');
            $command = sprintf(
                '%s %s %s %s %s %s',
                PHP_BINARY,
                escapeshellarg($scriptPath),
                escapeshellarg($className),
                escapeshellarg($methodName),
                escapeshellarg(json_encode($parameters)),
                escapeshellarg($retryCount)
            );

            // Execute based on platform
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen("start /B {$command} > NUL 2>&1", 'r'));
            } else {
                exec("{$command} > /dev/null 2>&1 &");
            }

            $logger->logSuccess(
                $className,
                $methodName,
                'Background job dispatched on ' . PHP_OS . ' platform'
            );

        } catch (\Throwable $e) {
            $logger->logFailure($className, $methodName, $e);
            throw $e;
        }
    }
}
