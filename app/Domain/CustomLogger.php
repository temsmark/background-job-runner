<?php

namespace App\Domain;

use Throwable;

class CustomLogger
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/background_jobs_errors.log');
    }

    /**
     * @param $jobClass
     * @param $method
     * @return void
     */
    public function logSuccess($jobClass, $method): void
    {
        $this->writeLog("SUCCESS: $jobClass::$method");
    }

    /**
     * @param $jobClass
     * @param $method
     * @param Throwable $exception
     * @return void
     */
    public function logFailure($jobClass, $method, Throwable $exception): void
    {
        $this->writeLog("FAILURE: $jobClass::$method - " . $exception->getMessage());
    }

    /**
     * @param $message
     * @return void
     */
    private function writeLog($message): void
    {
        $logEntry = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
        file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }
}
