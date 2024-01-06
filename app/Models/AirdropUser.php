<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirdropUser extends Model
{
    use HasFactory;
    
    protected $table = 'airdrop_registers'; // Set the table name
    protected $primaryKey = 'address';
}
