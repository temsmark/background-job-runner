<?php

use App\Domain\CustomLogger;
use App\Domain\ExampleJob;
use App\Domain\Runner\CustomBackgroundJobRunner;
use App\Models\BackgroundJob;
use Tests\TestCase;

class BackgroundJobTest extends TestCase
{
    private CustomBackgroundJobRunner $runner;
    private CustomLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = new CustomLogger();
        $this->runner = new CustomBackgroundJobRunner($this->logger);
    }

    public function testBasicJobExecution()
    {
        // Arrange
        $params = ['test' => 'data'];

        // Act
        runBackgroundJob(ExampleJob::class, 'run', $params);

        // Allow time for background process
        sleep(2);

        // Assert
        $job = BackgroundJob::latest()->first();
        $this->assertEquals('started', $job->status);
        $this->assertEquals(ExampleJob::class, $job->class_name);
        $this->assertEquals('run', $job->method_name);
        $this->assertEquals($params, $job->parameters);
    }


}
