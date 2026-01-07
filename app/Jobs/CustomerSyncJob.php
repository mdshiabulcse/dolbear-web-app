<?php

namespace App\Jobs;

use App\Repositories\Erp\CustomerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CustomerSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $customer;
    public function __construct($user)
    {
        $this->customer = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repo = app(CustomerRepository::class);

        $existingCustomer = null;

        if($this->customer->phone){
            $existingCustomer = $repo->findByMobile($this->customer->phone);
        }

        if ($existingCustomer) {

            $res = $repo->update($existingCustomer['name'], [
                'first_name' => $this->customer->first_name,
                'last_name'  => $this->customer->last_name,
                'phone'      => $this->customer->phone,
                'gender'      => $this->customer->gender ?? null,
            ]);

        } else {
            $res = $repo->store([
                'first_name' => $this->customer->first_name,
                'last_name'  => $this->customer->last_name,
                'phone'      => $this->customer->phone,
                'gender'      => $this->customer->gender ?? null,
            ]);

        }

        if ($res && isset($res['data']['name'])) {
            $this->customer->code = $res['data']['name'];
            $this->customer->save();
        }
    }
}
