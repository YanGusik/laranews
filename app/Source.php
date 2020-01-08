<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $guarded = [];


    public function posts()
    {
        return $this->hasMany('App\Post');
    }
}
