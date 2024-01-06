<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirdropRef extends Model
{
    use HasFactory;
    
    protected $table = 'airdrop_referral'; // Set the table name
    // protected $primaryKey = 'address';
}
