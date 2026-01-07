<?php

namespace App\Jobs;

use App\Repositories\Erp\AddressRepository;
use App\Repositories\Erp\CustomerRepository;
use App\Repositories\Erp\OrderErpRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $order;
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userId = $this->order->user_id;

        sleep(10);

        // No pending address or address already synced → continue order sync
        $repo = app(OrderErpRepository::class);
        $repo->store($this->order);

        info("Order {$this->order->id} synced successfully.");
    }


}
