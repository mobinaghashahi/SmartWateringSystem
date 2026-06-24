<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Devices extends Model
{
    use HasFactory, Notifiable, HasApiTokens, Notifiable;
    // Add fillable properties
    protected $fillable = ['uuid', 'last_check', 'last_update','customer_name','wifi_username','wifi_password'];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';


}
