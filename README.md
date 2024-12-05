# Background Jobs Documentation

## Overview
The background job system provides a way to asynchronously execute PHP classes and methods in Laravel applications. Jobs are executed in separate processes, with built-in support for logging, retries, and error handling.

## Basic Usage

### Running a Background Job
To execute a class method as a background job, use the `runBackgroundJob()` helper function:

```php
runBackgroundJob(
    className: ExampleJob::class,
    methodName: 'run',
    parameters: ['key' => 'value'],
    retryCount:3
);
```

### Required Parameters
- `className`: The fully qualified class name to execute (string)
- `methodName`: The method name to call on the class (string)
- `parameters`: Array of parameters to pass to the method (optional)
- `retryCount`: Number of current retry attempt (optional, defaults to 0)

## Security

### Approved Classes
For security reasons, only pre-approved classes can be executed as background jobs. Classes must be added to the `$approvedClasses` array in `CustomBackgroundJobRunner`:

```php
protected array $approvedClasses = [
    ExampleJob::class,
    // Add additional approved classes here
];
```

### Class Validation
The system performs several security checks:
1. Verifies the class is in the approved list
2. Confirms the class exists
3. Validates the method exists on the class
4. Sanitizes class and method names to prevent code injection

## Retry Configuration

### Default Settings
```php
protected const MAX_RETRIES = 3;    // Maximum number of retry attempts
protected const RETRY_DELAY = 5;    // Delay between retries in seconds
```

To modify these settings, extend the `CustomBackgroundJobRunner` class:

```php
class CustomRunner extends CustomBackgroundJobRunner 
{
    protected const MAX_RETRIES = 5;
    protected const RETRY_DELAY = 10;
}
```

## Logging

### Success Logging
Successful job execution is logged to:
- Console output
- `storage/logs/background_jobs_errors.log`
- Database (`job_logs`,`background_jobs` table)


### Error Logging
Failed jobs are logged with:
- Exception details
- Class and method information
- Retry attempt count
- Timestamp

## Examples

### Basic Job Example
```php
namespace App\Jobs;

class EmailNotification 
{
    public function send(array $params): void 
    {
        // Send email logic here
    }
}

// Execute the job
runBackgroundJob(
    EmailNotification::class,
    'send',
    ['to' => 'user@example.com', 'subject' => 'Hello'],
    3
);
```




## Job Status Tracking

Jobs can be tracked through the `background_jobs` table, which includes:
- Job ID
- Class and method names
- Parameters
- Status (pending, running, completed, failed)
- Retry count
- Execution timestamps

## Best Practices

1. **Job Classes**
    - Keep job classes focused on a single responsibility
    - Implement proper error handling
    - Use dependency injection when needed

2. **Parameters**
    - Pass only serializable data as parameters
    - Keep parameter size reasonable
    - Don't pass sensitive information directly

3. **Error Handling**
    - Log meaningful error messages
    - Implement proper cleanup in case of failures
    - Consider implementing job-specific retry logic

4. **Monitoring**
    - Regularly check the error logs
    - Monitor the job queue for failed jobs
    - Set up alerts for critical job failures

## Limitations

1. Only approved classes can be executed
2. Parameters must be JSON-serializable
3. No real-time job status updates
4No job dependencies or chaining

## Troubleshooting

### Common Issues

1. **Job Not Executing**
    - Verify the class is in the approved list
    - Check file permissions for the worker script
    - Ensure proper PHP path configuration

2. **Failed Retries**
    - Check the logs for specific error messages
    - Verify the retry delay and count configuration
    - Ensure proper error handling in the job class

3. **Missing Logs**
    - Check storage directory permissions
    - Verify log path configuration
    - Ensure database connectivity for job logs


# Job Monitor Interface Documentation

## Overview
The job monitoring interface provides a real-time, searchable view of background job logs using Laravel Livewire. It features a responsive table layout with pagination, search functionality, and visual status indicators.

## Features
- Real-time updates using Livewire
- Searchable job messages
- Pagination support
- Visual status indicators (color-coded)
- Relative timestamp display
- Responsive table layout

## Implementation

### Livewire Component

The job monitor is implemented using a Livewire component (`JobListLivewire`) with the following features:

```php
use Livewire\WithPagination;

class JobListLivewire extends Component
{
    use WithPagination;

    public string $search = '';     // Search query string
    public int $perPage = 10;       // Items per page
    
    public function render()
    {
        $jobs=JobLog::query()
            ->when($this->search, fn($query) =>
            $query->where('message', 'like', '%' . $this->search . '%')
            )
            ->with('job')
            ->latest()
            ->paginate($this->perPage);


        return view('livewire.job-list-livewire',compact('jobs'));
    }
}
```

### Search Functionality
The search feature filters job logs based on their messages:
- Debounced input (300ms) to prevent excessive database queries
- Case-insensitive partial matching
- Real-time results updating

### Table Columns
The monitor displays the following information for each job:
1. Job ID
2. Status Type (color-coded)
3. Message
4. Job Details
5. Creation Time (relative format)

## Status Types and Styling

Status types are visually differentiated using color-coded badges:

| Status   | Style Classes                           | Background Color |
|----------|----------------------------------------|-----------------|
| Success  | `bg-green-100 text-green-800`          | Light Green     |
| Failure  | `bg-red-100 text-red-800`              | Light Red       |
| Info     | `bg-blue-100 text-blue-800`            | Light Blue      |

## Installation

1. Ensure Livewire is installed:
```bash
composer require livewire/livewire
```

2. Include Livewire scripts in your layout:
```html
@livewireStyles
@livewireScripts
```

3. Register the component in your routes or view:
    - uses the default dashboard route
```php
// In a blade view
<livewire:job-list-livewire />

// Or in routes/web.php
```
## Usage

### Viewing Job Logs
- Navigate to the dashboards page 
- Logs are displayed in reverse chronological order
- Each entry shows:
    - Job identifier
    - Status type with color indicator
    - Message content
    - Associated job details
    - Relative timestamp

### Searching Logs
1. Enter search terms in the search box
2. Results update automatically after 300ms
3. Search matches against job messages
4. Clear the search box to reset results



### Modifying Items Per Page
Update the `$perPage` property in `JobListLivewire`:

```php
public int $perPage = 25; // Change to desired number
```

## Best Practices

1. **Performance**
    - Keep search debounce time to prevent excessive queries
    - Use eager loading for job relationships
    - Index frequently searched columns

2. **UI/UX**
    - Maintain responsive design for mobile views
    - Use clear visual indicators for status
    - Keep message column readable with proper wrapping

3. **Maintenance**
    - Regularly clean old log entries
    - Monitor database size
    - Consider archiving old logs

## Troubleshooting

### Common Issues

1. **Search Not Updating**
    - Verify Livewire scripts are properly included
    - Check browser console for JavaScript errors
    - Ensure debounce value isn't too high

2**Performance Issues**
    - Add database indexes for searched columns
    - Optimize eager loading relationships
    - Consider implementing caching


## Sample Logs
 ``` logs
[2024-12-04 1 4:31:53] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 14:31:53] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 14:31:53] SUCCESS: App\Domain\ExampleJob::run - {"class_name":"App\\Domain\\ExampleJob","method_name":"run","parameters":[{"param1":"value"}],"status":"pending","retry_count":3,"scheduled_at":"2024-12-04T14:31:53.000000Z","updated_at":"2024-12-04T14:31:53.000000Z","created_at":"2024-12-04T14:31:53.000000Z","id":12}
[2024-12-04 19:41:49] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:41:49] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:41:49] SUCCESS: App\Domain\ExampleJob::run - {"class_name":"App\\Domain\\ExampleJob","method_name":"run","parameters":[{"param1":"value"}],"status":"started","retry_count":3,"scheduled_at":"2024-12-04T19:41:49.000000Z","updated_at":"2024-12-04T19:41:49.000000Z","created_at":"2024-12-04T19:41:49.000000Z","id":13,"started_at":"2024-12-04T19:41:49.000000Z"}
[2024-12-04 19:43:38] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:43:38] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:43:38] SUCCESS: App\Domain\ExampleJob::run - Background job started
[2024-12-04 19:43:38] SUCCESS: App\Domain\ExampleJob::run - {"class_name":"App\\Domain\\ExampleJob","method_name":"run","parameters":[{"param1":"value"}],"status":"started","retry_count":3,"scheduled_at":"2024-12-04T19:43:38.000000Z","updated_at":"2024-12-04T19:43:38.000000Z","created_at":"2024-12-04T19:43:38.000000Z","id":14,"started_at":"2024-12-04T19:43:38.000000Z"}
[2024-12-04 19:44:50] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:44:50] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:44:50] SUCCESS: App\Domain\ExampleJob::run - Background job started
[2024-12-04 19:44:50] SUCCESS: App\Domain\ExampleJob::run - Background job completed processing
[2024-12-04 19:50:59] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:50:59] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:51:15] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:51:15] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:51:16] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:51:16] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:51:17] SUCCESS: App\Domain\ExampleJob::run - Background job initiated
[2024-12-04 19:51:17] SUCCESS: App\Domain\ExampleJob::run - Background job dispatched on Linux platform
[2024-12-04 19:51:51] FAILURE: App\Domain\ExampleJob::run - Worker script not found
```
# Folder Structure Documentation

## Overview
The background jobs system is organized under the `Domain` namespace with the following structure:

```
Domain/
├── Exceptions/
│   └── JobRunnerException.php
├── Runner/
│   └── CustomBackgroundJobRunner.php
├── CustomLogger.php
├── ExampleJob.php
└── helper.php
```

## Directory Purpose and Contents

### Domain/
The root directory containing all background job related functionality.

### Domain/Exceptions/
Contains custom exceptions for the background jobs system.

#### JobRunnerException.php
- Custom exception class for background job specific errors
- Used for handling job execution failures, validation errors, and security violations
- Provides specific error messaging for job-related issues

### Domain/Runner/
Contains the core job execution logic.

#### CustomBackgroundJobRunner.php
- Main job execution engine
- Handles:
    - Job validation and security checks
    - Execution of background processes
    - Retry logic
    - Job status management

