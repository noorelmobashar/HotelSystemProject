<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{

    public function manager()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
