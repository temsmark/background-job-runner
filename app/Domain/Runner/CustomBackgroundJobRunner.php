<?php

namespace App\Domain\Runner;

use App\Domain\CustomException;
use App\Domain\CustomLogger;
use App\Domain\ExampleJob;
use App\Domain\Exceptions\JobRunnerException;

class CustomBackgroundJobRunner
{
    private CustomLogger $logHandler;

    public function __construct()
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



    public function run($class, $method, $params=[]): void
    {
        if (!in_array($class, $this->approvedClasses)) {
            $this->logHandler->logFailure(
                $class,
                $method, new \Exception('Class not approved for background job'));
            return;
        }


        if (!$this->validate($class, $method)) {
            return;
        }

        logger('Running background job', [$class, $method, $params]);
        try {
            $classInstance = app($class);
            $classInstance->$method(...$params);
            $this->logHandler->logSuccess($class, $method);
        } catch (\Throwable $e) {
            $this->logHandler->logFailure($class, $method, $e);
        }



    }




    /**
     * Validate and sanitize class and method names to prevent execution of unauthorized or harmful code.
     * @param $class
     * @param $method
     * @return bool
     */
    public function validate($class, $method): bool
    {
        if (!class_exists($class)) {
            $this->logHandler->logFailure(
                $class,
                $method,
                new JobRunnerException('Class not found'));
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


    public static function handleCliExecution(array $args): void
    {
        $logHandler = new CustomLogger();
        try {
            if (count($args) < 4) {
                $logHandler->logFailure(
                    'CustomBackgroundJobRunner',
                    'handleCliExecution',
                    new JobRunnerException('Insufficient arguments'.implode(' ', $args))
                );
            }

            $className = $args[1];
            $methodName = $args[2];
            $jsonParams = $args[3];

            // Additional validation
            if (!class_exists($className)) {
                $logHandler->logFailure(
                    'CustomBackgroundJobRunner',
                    'handleCliExecution',
                    new JobRunnerException("Class does not exist: {$className}")
                );
            }

            $params = json_decode($jsonParams, true);

            $runner = new self();
            $runner->run($className, $methodName, $params ?? []);

            exit(0);

        } catch (\Throwable $e) {
            $logHandler->logFailure(
                'CustomBackgroundJobRunner',
                'handleCliExecution',
                $e);
            exit(1);
        }

    }

}

// CLI execution
if (PHP_SAPI === 'cli') {
    require_once __DIR__ . '/../../../vendor/autoload.php';
    CustomBackgroundJobRunner::handleCliExecution($argv);
}
