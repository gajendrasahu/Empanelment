<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalContact extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'Pers_Contacts';
    public function records()
    {
        return $this->hasMany(PersonalContactOccassion::class, 'ContactID');
    }    

}
