<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    protected $guarded = [];
    public function manager()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
