<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'Vouchers';

    protected $fillable = ['VoucherTypeID','VoucherNo','FinYear','VoucherNoString','VoucherDate','AccountID','PInvNo','PInvDate','PayModeID','TotalAmount','GstValue','Packing','PackingGstValue','Freight','FreightGstValue','Roundup','NetAmount','Remarks','Transport','CreditDays','UserID','CancelStatus','EntryDateTime','Discount','WCustomerName','WCustomerID','OtherCharges','AgainstAccID'];

}
