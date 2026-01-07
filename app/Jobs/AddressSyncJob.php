<?php

namespace App\Jobs;

use App\Repositories\Erp\AddressRepository;
use App\Repositories\Erp\CustomerRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddressSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $address;
    public function __construct($address)
    {
        $this->address = $address->load('user');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repo = app(AddressRepository::class);

        info('dispatched erp sync address job');

//        $existing = $repo->checkAddress($this->address->user->code);

        $res = $repo->store([
            'phone' => $this->address->phone_no,
            'email' => $this->address->email,
            'city' => $this->address->district,
            'state'  => $this->address->division,
            'address_title'  => $this->address->phone_no,
            'address_line'      => $this->address->address,
            'link_name'      => $this->address->user->code,
        ]);

        if ($res && isset($res['data']['name'])) {
            $this->address->erp_code = $res['data']['name'];
            $this->address->save();
        }
    }
}
