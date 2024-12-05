<?php

namespace App\Domain;

use App\Models\BackgroundJob;
use App\Models\JobLog;
use Throwable;

class CustomLogger
{
    private string $logPath;

    public function __construct()
    {
        $this->logPath = __DIR__ . '/../../storage/logs/background_jobs_errors.log';

        $logDir = dirname($this->logPath);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * @param $jobClass
     * @param $method
     * @param null $message
     * @param BackgroundJob|null $job
     * @return void
     */
    public function logSuccess($jobClass, $method,$message=null,?BackgroundJob $job = null): void
    {

        $logMessage = "SUCCESS: $jobClass::$method" . ($message ? " - $message" : '');
        $this->writeLog($logMessage);

        if ($job) {
            JobLog::create([
                'background_job_id' => $job->id,
                'type' => 'success',
                'message' => $message ?? 'Job executed successfully'
            ]);
        }
    }

    /**
     * @param $jobClass
     * @param $method
     * @param Throwable $exception
     * @param BackgroundJob|null $job
     * @return void
     */
    public function logFailure($jobClass, $method, Throwable $exception,?BackgroundJob $job = null): void
    {
        $this->writeLog("FAILURE: $jobClass::$method - " . $exception->getMessage());

        if ($job) {
            JobLog::create([
                'background_job_id' => $job->id,
                'type' => 'failure',
                'message' => $exception->getMessage()
            ]);
        }
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
