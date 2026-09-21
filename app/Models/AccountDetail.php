<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'AccountDetails';
    
    protected $fillable = ['AccountID', 'DOB', 'DOM','QualificationID','SpecialisationID'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID');
    }
}
