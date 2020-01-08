<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    public function source()
    {
        return $this->belongsTo('App\Source');
    }

    public function getSourceNameAttribute()
    {
        return $this->source->name;
    }
}
