<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvTestPackage extends Model
{
    use HasFactory;
    protected $table = 'InvTestPackages';

    public function invtestpackage()
    {
        return $this->belongsTo(InvTest::class, 'InvTestID');
    }

}
