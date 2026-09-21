<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvTestProduct extends Model
{
    use HasFactory;
    protected $table = 'InvTestProducts';
    public function invtestproduct()
    {
        return $this->belongsTo(InvTestProduct::class, 'InvTestID');
    }

}
