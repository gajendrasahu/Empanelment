<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;
use Spatie\Async\Pool;

class CheckOrderStatus
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\OrderCreated  $event
     * @return void
     */
    public function handle(OrderCreated $event)
    {
        // Fetch the orders that need to be processed (this could be multiple orders, or just the one)
        $orders = DB::table('customer_order_detail')->where('orderstatus',0)->get();

        // Create a pool to manage concurrent processing
        $pool = Pool::create();

        foreach ($orders as $order) {
            // Add each order to the pool to process them concurrently
            $pool->add(function () use ($order) {
                $this->checkOrderStatus($order->detailid);
            });
        }

        // Wait for all tasks to complete
        $pool->wait();
    }

    /**
     * Check the status of a specific order.
     *
     * @param  int  $orderId
     * @return void
     */
    public function checkOrderStatus($detailid)
    {
		$ind=0;
        while(true)
		{
			$ind++;
            // Fetch the latest order status from the database
            $order 		=	DB::table('customer_order_detail')->where('detailid','=',$detailid)->first();
			$particular	=	$ind." LOOP";
			DB::insert('insert into testing_tbl(particular) values(?,?)',[$particular,$detailid]);
			
            // If the order status is 1, exit the loop
            if ($order->orderstatus == 1) {
                break;
            }

            // Optional: Sleep for a second before checking again
            sleep(30);
        }

        // Output the result (optional)
		echo "1";
		
    }
}
?>