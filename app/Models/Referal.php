<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referal extends Model
{
    use HasFactory;

    protected $table = 'referal'; // Set the table name
    protected $primaryKey = 'referal';
    public $timestamps = false;
}
