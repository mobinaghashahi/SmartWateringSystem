<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    // Add fillable properties
    protected $fillable = ['watering_state', 'command_date', 'uuid'];

}
