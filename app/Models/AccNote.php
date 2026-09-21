<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccNote extends Model
{
    use HasFactory;
    protected $table = 'AccNotes';

    protected $fillable = ['AccountID', 'Notes'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'AccountID');
    }

}
