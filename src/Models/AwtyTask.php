<?php

namespace Orrison\AreWeThereYet\Models;

use Illuminate\Database\Eloquent\Model;

class AwtyTask extends Model
{
    protected $table = 'awty_tasks';

    protected $guarded = ['id'];

    public function getJobAttribute($value) {
        return unserialize($value);
    }

    public function setJobAttribute($value) {
        $this->attributes['job'] = serialize($value);
    }
}
