<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device_version extends Model
{
    // Add fillable properties
    protected $fillable = ['update_installed', 'update_date', 'uuid','update_version','update_file_name'];
}
