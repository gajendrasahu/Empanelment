<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'Accounts';

    public function accfamilydetail()
    {
        return $this->hasMany(AccFamilyDetail::class, 'AccountID');
    }

    public function accnote()
    {
        return $this->hasMany(AccNote::class, 'AccountID');
    }

    public function accountdetail()
    {
        return $this->hasMany(AccountDetail::class, 'AccountID');
    }
    protected $fillable = ['AccountName', 'OpeningBalance', 'AccountGroupID','AccountStatusID','Address','ContactNo','EmailID','EntryDateTime','UserID','IsDisabled','NickName','Qualification','CertificateNo','AllowComplimentory','IsUnderMonitoring','MonitoringValue','CreditDays','Password','IsKey','AllowNoSWF','IsDailySWF','IsGuardian','CatID'];


}
