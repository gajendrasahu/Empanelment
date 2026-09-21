<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PurchaseOrderForm extends Component
{
    protected $debug = true;
    public $products = [];

    public function addProduct()
    {
        $this->products[] = ['productname' => '','quantity' => '','rate' => '','total' => '',
        ];
    }

    public function removeProduct($index)
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
    }

    public function render()
    {
        return view('livewire.purchase-order-form');
    }
}