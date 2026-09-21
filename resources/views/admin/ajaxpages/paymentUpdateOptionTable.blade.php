@php
$i=1;
$t=8;
@endphp
@if($paymentmode=='CASH')
<div class="col-sm-2">
	RECEIPT NO<label id="req">*</label>
	<input type="text" class="form-control" name="receiptnumber" id="receiptnumber" required value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Enter receipt no"/>
	<span class="text-danger">@error('receiptnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	PAYING AMOUNT<label id="req">*</label>
	<input type="number" class="form-control" name="amount" id="amount" required value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" placeholder="Paying amount" autocomplete="off" style="width:100%;" onchange="CalBalanceAmount(this.value)"/>
	<span class="text-danger">@error('amount') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	REMARK IF ANY<label id="req">&nbsp;</label>
	<input type="text" class="form-control" name="remark" id="remark" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Remark"/>
</div>
<div class="col-sm-2">
	<br>
	<button type="submit" class="btn btn-info" tabindex="<?php echo $t++;?>" onclick="this.disabled = true; this.form.submit();" style="padding:2px 10px; background-color:#9191FF; width:100%;">MAKE PAYMENT</button>
</div>
@endif

@if($paymentmode=='CHEQUE')
<div class="col-sm-2">
	CHEQUE NUMBER<label id="req">*</label>
	<input type="text" class="form-control" name="chequenumber" value="" required id="chequenumber" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Enter cheque number"/>
	<span class="text-danger">@error('chequenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	CHEQUE DATE<label id="req">*</label>
	<input type="date" class="form-control" name="chequedate" required id="chequedate" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;"/>
	<span class="text-danger">@error('chequedate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	PAYING AMOUNT<label id="req">*</label>
	<input type="number" class="form-control" required name="amount" value="" id="amount" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" placeholder="Paying amount" autocomplete="off" style="width:100%;" onchange="CalBalanceAmount(this.value)"/>
	<span class="text-danger">@error('amount') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-12">&nbsp;</div>
<div class="col-sm-10">
	REMARK IF ANY<label id="req">&nbsp;</label>
	<input type="text" class="form-control" name="remark" id="remark" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Remark"/>
</div>

<div class="col-sm-2">
	<br id="forbutton" />
	<button type="submit" class="btn btn-info" tabindex="<?php echo $t++;?>" onclick="this.disabled = true; this.form.submit();" style="padding:2px 10px; background-color:#9191FF; width:100%;">MAKE PAYMENT</button>
</div>
	
@endif

@if($paymentmode=='NEFT')
<div class="col-sm-2">
	BATCH NEFT<label id="req">*</label>
	<input type="text" class="form-control" name="batchneft" value="" required id="batchneft" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Enter NEFT no"/>
	<span class="text-danger">@error('batchneft') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	PAYING AMOUNT<label id="req">*</label>
	<input type="number" class="form-control" name="amount" id="amount" required value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" placeholder="Paying amount" style="width:100%;" onchange="CalBalanceAmount(this.value)"/>
	<span class="text-danger">@error('amount') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	REMARK IF ANY<label id="req">&nbsp;</label>
	<input type="text" class="form-control" name="remark" id="remark" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Remark"/>
</div>
<div class="col-sm-2">
<br>
<button type="submit" class="btn btn-info"tabindex="<?php echo $t++;?>" onclick="this.disabled = true; this.form.submit();" style="padding:2px 10px; background-color:#9191FF; width:100%;">MAKE PAYMENT</button>
</div>
@endif

@if($paymentmode=='RTGS')
<div class="col-sm-2">
	UTR NO<label id="req">*</label>
	<input type="text" class="form-control" name="utrno" value="" required id="utrno" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="UTR No"/>
	<span class="text-danger">@error('utrno') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	PAYING AMOUNT<label id="req">*</label>
	<input type="number" class="form-control" name="amount" required id="amount" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" placeholder="Paying amount" autocomplete="off" style="width:100%;" onchange="CalBalanceAmount(this.value)"/>
	<span class="text-danger">@error('amount') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
</div>
<div class="col-sm-2">
	REMARK IF ANY<label id="req">&nbsp;</label>
	<input type="text" class="form-control" name="remark" id="remark" value="" onKeyPress="return OnKeyPress(this, event)" tabindex="<?php echo $t++;?>" autocomplete="off" style="width:100%;" placeholder="Remark"/>
</div>
<div class="col-sm-2">
<br>
<button type="submit" class="btn btn-info" tabindex="<?php echo $t++;?>" onclick="this.disabled=true; this.form.submit();" style="padding:2px 10px; background-color:#9191FF; width:100%;">MAKE PAYMENT</button>
</div>
@endif

@if($paymentmode=='CREDIT')
<div class="col-sm-2" style="text-align:left;">
	<br id="forbutton" />
	<button type="submit" class="btn btn-info" tabindex="<?php echo $t++;?>" onclick="this.disabled = true; this.form.submit();" style="padding:2px 10px; background-color:#9191FF; width:100%;">SAVE RECORD</button>
</div>
@endif