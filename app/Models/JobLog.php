<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $background_job_id
 * @property string $type
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BackgroundJob $job
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereBackgroundJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|JobLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class JobLog extends Model
{

    protected $fillable = [
        'background_job_id',
        'type',
        'message'
    ];

    protected $guarded = ['id'];

    public function job()
    {
        return $this->belongsTo(BackgroundJob::class, 'background_job_id');
    }
}
