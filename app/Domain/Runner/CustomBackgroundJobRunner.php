<?php

namespace App\Domain\Runner;

use App\Domain\CustomLogger;
use App\Domain\ExampleJob;
use App\Domain\Exceptions\JobRunnerException;
use App\Models\BackgroundJob;

class CustomBackgroundJobRunner
{

    // Configuration for job execution
    protected const MAX_RETRIES = 3;
    protected const RETRY_DELAY = 5;

    public function __construct(private CustomLogger $logHandler)
    {
        $this->logHandler = new CustomLogger();
    }

    /**
     *Security
     * Ensure that only pre-approved classes can be run in the background for security reasons
     * @var array $approvedClasses
     */
    protected array $approvedClasses = [
        ExampleJob::class
    ];


    /**
     * @param string $class
     * @param string $method
     * @param array $params
     * @param int $currentRetry
     * @return void
     */
    private function run(string $class, string $method, array $params = [], int $currentRetry = 0): void
    {
        $job = BackgroundJob::create([
            'class_name' => $class,
            'method_name' => $method,
            'parameters' => $params,
            'status' => 'pending',
            'retry_count' => $currentRetry,
            'scheduled_at' => now(),
        ]);

        $job->markAsStarted('Background job started');
        $this->logHandler->logSuccess($class, $method, 'Background job started', $job);


        // Security check
        if (!in_array($class, $this->approvedClasses)) {
            $job->markAsFailed('Class not approved for background job');
            $this->logHandler->logFailure(
                $class,
                $method,
                new JobRunnerException('Class not approved for background job security check'),
                $job
            );
            return;
        }

        // Validate class and method
        if (!$this->validate($class, $method,$job)) {
            return;
        }

        try {

            // Create instance and execute
            $classInstance = new $class();
            $classInstance->$method(...$params);

            $this->logHandler->logSuccess($class, $method, 'Background job completed processing', $job);
            $job->markAsCompleted('Background job completed processing');


        } catch (\Throwable $e) {
            $this->logHandler->logFailure($class, $method, $e,$job);

            // Handle retry logic
            if ($currentRetry < self::MAX_RETRIES) {
                $this->retryJob($class, $method, $params, $currentRetry + 1,$job);
            }
        }



    }


    /**
     * Validate and sanitize class and method names to prevent execution of unauthorized or harmful code.
     * @param $class
     * @param $method
     * @param BackgroundJob|null $job
     * @return bool
     */
    private function validate($class, $method,?BackgroundJob $job): bool
    {
        if (!class_exists($class)) {
            $this->logHandler->logFailure(
                $class,
                $method,
                new JobRunnerException('Class not found'),
                $job
            );
            return false;
        }

        if (!method_exists($class, $method)) {
            $this->logHandler->logFailure(
                $class,
                $method,
                new JobRunnerException('Method not found'));
            return false;
        }
        return true;
    }

    /**
     * @param string $class
     * @param string $method
     * @param array $params
     * @param int $retryCount
     * @param BackgroundJob|null $job
     * @return void
     */
    protected function retryJob(string $class, string $method, array $params, int $retryCount,?BackgroundJob $job): void
    {
        sleep(self::RETRY_DELAY);

        // Build command for retry
        $command = sprintf(
            'php %s %s %s %s %d',
            base_path('worker.php'),
            escapeshellarg($class),
            escapeshellarg($method),
            escapeshellarg(json_encode($params)),
            $retryCount
        );

        // Execute retry based on platform
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B {$command} > NUL 2>&1", 'r'));
        } else {
            exec("{$command} > /dev/null 2>&1 &");
        }

        $this->logHandler->logSuccess(
            $class,
            $method,
            "Scheduled retry attempt {$retryCount} of " . self::MAX_RETRIES,
            $job
        );
    }

    /**
     * @param array $args
     * @return void
     * @throws JobRunnerException
     */
    public function handleCliExecution(array $args): void
    {
        [$class, $method, $jsonParams, $retryCount] = $args;
        $params = json_decode($jsonParams, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JobRunnerException('Invalid JSON parameters');
        }

        $this->run($class, $method, $params, (int)$retryCount);
    }






}



