<?php

namespace App\Models;

use App\Jobs\AddressSyncJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAddress extends Model
{
    protected $guarded = [];
    protected $table = 'delivery_addresses';

    protected $casts = [
        'address_ids' => 'array',
    ];

    protected static function booted()
    {
//        static::created(function ($address) {
//            AddressSyncJob::dispatch($address);
//        });

        static::updated(function ($address) {
            if ($address->isDirty(['phone_no', 'email', 'division', 'district', 'address'])) {
                cache()->put("address_synced_pending_{$address->user->id}", true, 180);
                AddressSyncJob::dispatch($address);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
