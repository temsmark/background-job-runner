<?php

use App\Domain\CustomLogger;
use App\Domain\Exceptions\JobRunnerException;

if (!function_exists('runBackgroundJob')) {
    /**
     * @param string $className
     * @param string $methodName
     * @param array $parameters
     * @return void
     * @throws Exception
     */
    function runBackgroundJob(string $className, string $methodName, array $parameters = []): void
    {
        $logger = new CustomLogger();
        $scriptPath = base_path('app/Domain/Runner/CustomBackgroundJobRunner.php');

        if (!file_exists($scriptPath)) {
            $logger->logFailure(
                $className,
                $methodName,
                new JobRunnerException('Background job runner script not found'
                )
            );
        }

        $jsonParams = escapeshellarg(json_encode($parameters));
        $command = sprintf(
            'php %s %s %s %s',
            $scriptPath,
            escapeshellarg($className),
            escapeshellarg($methodName),
            $jsonParams
        );


        // Platform-specific background execution
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B {$command}", 'r'));
        } else {
//            exec("{$command} > /dev/null 2>&1 &");
            exec("{$command} ");
            logger($command);
        }
    }
}
