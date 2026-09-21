<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'POrders';

    protected $fillable = ['FinYear','POrderNo','POorderNoString','POrderDate','AccountID','DeliveryDate','ShippingAddress','TotalAmount','GstValue','Packing','PackingGstValue','Freight','FreightGstValue','Roundup','NetAmount','Remarks','Transporter','CreditDays','ExecutionStatusID','UserID','CancelStatus','EntryDateTime','Discount'];


}
