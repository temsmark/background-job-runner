<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $class_name
 * @property string $method_name
 * @property array|null $parameters
 * @property string $status
 * @property int $retry_count
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereClassName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereMethodName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereRetryCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BackgroundJob whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BackgroundJob extends Model
{

    protected $fillable = [
        'class_name',
        'method_name',
        'parameters',
        'status',
        'retry_count',
        'scheduled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @param string $reason
     * @return void
     */
    public function markAsFailed(string $reason): void
    {
        $this->status = 'failed';
        $this->completed_at = now();
        $this->save();
    }

    /**
     * @return void
     */
    public function markAsStarted(): void
    {
        $this->status = 'started';
        $this->started_at = now();
        $this->save();
    }

    /**
     * @return void
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }
}
