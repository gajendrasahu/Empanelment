<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalContactOccassion extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'Pers_ContOcc';    
    protected $fillable = ['ContactID','OccassionID'];
    public function contacts()
    {
        return $this->belongsTo(PersonalContact::class, 'ContactID');
    }    
}
