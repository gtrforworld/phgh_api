<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhTrx extends Model
{
    use HasFactory;

    protected $table = 'ph_transactions'; // Set the table name
    // protected $primaryKey = 'wallet';
}
