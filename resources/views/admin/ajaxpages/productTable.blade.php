@php $r=0; @endphp
@foreach($product as $prod)
@php $r++; @endphp
<tr id="item-{{ $r }}" style="display:none!important;"><td>
<tr class="myheadbg">
	<td colspan="6">{{$r}}) {{$prod->productname}}
		<span style="float:right; margin-right:20px;">
		@if(in_array(2,Session::get('actions')))
			<a href="{{ route('product.edit',$prod->productid) }}" style="color:white;">
				<i class="fa fa-edit"></i>
			</a>
		@endif
			|
		@if(in_array(3,Session::get('actions')))
			<i class="fa fa-trash" onclick="deleteItem('product',{{ $prod->productid}},{{ $r }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','EXPENSE SUB HEAD NAME DELETED SUCCESSFULLY')"></i>
		@endif
		</span>
	</td>
</tr>
<tr style="font-weight:bold;">
	<td>CATEGORY NAME</td>
	<td>BRAND NAME</td>
	<td>HSN CODE</td>
	<td>TAX</td>
	<td>UNIT</td>
	<td>SALES PRICE INCLUDING TAX</td>
</tr>
<tr>
	<td>{{$prod->category}}</td>
	<td>{{$prod->brandname}}</td>
	<td>{{$prod->hsncode}}</td>
	<td>{!!$prod->displayname!!}</td>
	<td>{{$prod->unitname}}</td>
	<td>@if($prod->issalesincludingtax==0) NO @else YES @endif</td>
</tr>
@if($prod->description!='')
<tr style="font-weight:bold;">
	<td colspan="6">DESCRIPTION</td>
</tr>
<tr>
	<td colspan="6">{!!$prod->description!!}</td>
</tr>
@endif
@if($prod->specification!='')
<tr style="font-weight:bold;">
	<td colspan="6">SPECIFICATION</td>
</tr>
<tr>
	<td colspan="6">{!!$prod->specification!!}</td>
</tr>
@endif
@if(count($prod->variants)>0)
<tr>
	<td colspan="6" style="padding:0px;">
		<table style="width:100%; border:1px solid #eee; border-collapse:collapse;" border="1">
			<tr class="myheadbg">
				<td>VARIATION NAME</td>
				<td>VARIATION VALUE</td>
				<td style="text-align:center;">ITEM CODE</td>
				<td style="text-align:center;">MRP</td>
				<td style="text-align:center;">SALES PRICE</td>
				<td style="text-align:center;">QUANTITY</td>
				<td style="text-align:center;">ALERT QUANTITY</td>
				<td style="text-align:center;">OPENING STOCK</td>
				<td style="text-align:center;">OP STOCK DATE</td>
			</tr>
			@foreach($prod->variants as $var)
			<tr>
				<td>{{$var->variationname}}</td>
				<td>{{$var->variationvalue}}</td>
				<td style="text-align:center;">{{$var->itemcode}}</td>
				<td style="text-align:center;">{{$var->mrp}}</td>
				<td style="text-align:center;">{{$var->salesprice}}</td>
				<td style="text-align:center;">{{$var->quantity}}</td>
				<td style="text-align:center;">{{$var->quantityalert}}</td>
				<td style="text-align:center;">{{$var->openingstock}}</td>
				<td style="text-align:center;">@if($var->opstdate!='') {{date('d\-m\-Y',strtotime($var->opstdate))}} @endif</td>
			</tr>
			@endforeach
		</table>
	</td>
</tr>
@endif
@if(count($prod->images)>0)
<tr>
	<td colspan="6" style="padding:0px;">
@foreach($prod->images as $img)						
<a href="#" onclick="openImageWindow('{{ asset('storage/'.$img->productimage) }}','PRODUCT IMAGE'); return false;">
<img src="{{ asset('storage/'.$img->productimage) }}" alt="Image" style="width:100px; height:100px; position:relative; margin-bottom:15px;">
</a>
@endforeach
	</td>
</tr>
@endif
</td></tr>
@endforeach
@if($r==0)
<tr>
    <td colspan="6" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@else
<tr>
<td colspan="6" style="text-align:right;">
{{ $product->links('vendor.pagination.default') }}
</td>
</tr>
@endif

<script>
const paginationLinks = document.querySelectorAll('.pagination a');
paginationLinks.forEach(link => {
	link.onclick = function(event) {
		event.preventDefault();
		const page = this.getAttribute('href').split('page=')[1];
		const url = '{{ route('productlist.html') }}';
		loadData(page, url);
	};
});
</script>
