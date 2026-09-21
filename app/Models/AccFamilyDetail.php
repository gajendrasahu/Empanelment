<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccFamilyDetail extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table    = 'AccFamilyDetails';
    protected $fillable = ['AccountID', 'MemberName', 'DOB','GenderID','RelationID'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID');
    }

}
