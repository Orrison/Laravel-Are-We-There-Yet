<?php

namespace Orrison\AreWeThereYet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TaskedJob extends Model
{
    public const STATUS_STARTED = 'started';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function taskable()
    {
        return $this->morphTo('taskable');
    }

    /**
     * Mark the step as started
     *
     * @return void
     */
    public function markAsStarted()
    {
        if (!$this->hasStarted()) {
            $this->update([
                'status' => static::STATUS_STARTED,
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Whether the job has already started.
     *
     * @return boolean
     */
    public function hasStarted()
    {
        return !empty($this->started_at);
    }

    /**
     * Mark the step as finished
     *
     * @return void
     */
    public function markAsFinished()
    {
        $this->update([
            'status' => static::STATUS_FINISHED,
            'finished_at' => now(),
        ]);
    }

    /**
     * Whether this job has finished or failed
     *
     * @return boolean
     */
    public function hasFinished()
    {
        return !empty($this->finished_at);
    }

    /**
     * Mark the step as failed
     *
     * @return void
     */
    public function markAsFailed($exception)
    {
        $this->update([
            'status' => static::STATUS_FAILED,
            'finished_at' => now(),
        ]);

        $this->setOutput($exception);

        if (method_exists($this->taskable, 'markAsFailed')) {
            $this->taskable->markAsFailed();
        }
    }

    /**
     * Whether the job has failed.
     *
     * @return boolean
     */
    public function hasFailed()
    {
        return $this->status == static::STATUS_FAILED;
    }

    /**
     * Get the duration of the job, in human diff.
     *
     * @return string
     */
    public function duration()
    {
        if (!$this->hasStarted()) return '';

        return ($this->finished_at ?? now())
            ->diffAsCarbonInterval($this->started_at)
            ->forHumans(['short' => true]);
    }

    /**
     * Get a pretty formatted label based on the name of the job.
     *
     * @return string
     */
    public function label(): string
    {
        return str_replace('-', ' ', Str::title(Str::kebab($this->task_name)));
    }
}
