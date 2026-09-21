<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvTest extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'InvTests';

        
    public function invtestproduct()
    {
        return $this->hasMany(InvTestProduct::class, 'InvTestID');
    }

    public function invtestpackage()
    {
        return $this->hasMany(InvTestPackage::class, 'InvTestID');
    }

}
