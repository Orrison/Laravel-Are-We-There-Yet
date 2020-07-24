<?php

namespace Orrison\AreWeThereYet\Models;

use Illuminate\Database\Eloquent\Model;

class AwtyGoal extends Model
{
    protected $table = 'awty_goals';

    protected $guarded = ['id'];

    public function getCompletionJob($value) {
        return unserialize($value);
    }

    public function setCompletionJob($value) {
        $this->attributes['completionJob'] = serialize($value);
    }
}
