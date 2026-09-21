<div>
    <button wire:click="addProduct">Add Product</button>
    @foreach($products as $index => $product)
        <div>
            <input type="text" wire:model="products.{{ $index }}.productname" placeholder="Product Name">
            <input type="number" wire:model="products.{{ $index }}.quantity" placeholder="Quantity">
            <input type="number" wire:model="products.{{ $index }}.rate" placeholder="Rate">
            <input type="number" wire:model="products.{{ $index }}.total" placeholder="Total">
            <button wire:click="removeProduct({{ $index }})">Remove</button>
        </div>
    @endforeach
</div>
@livewireScripts