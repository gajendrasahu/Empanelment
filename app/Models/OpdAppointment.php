<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpdAppointment extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table    = 'OPDAppointments';
    protected $fillable = ['OPDVisitPlanID','OPDPatientID','CurWt','CurHeight','BP','Temprature','Problem','History','AmtPayable','AmtPaid','Discount','AmtBalance','UserID','EntryDateTime','AmtPaidToDr','CancelStatus'];
    
}
