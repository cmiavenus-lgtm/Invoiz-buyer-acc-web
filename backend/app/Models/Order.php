<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'buyer_id',
        'seller_id',
        'address_id',
        'total',
        'total_amount',
        'sub_total',
        'discount_total',
        'shipping_fee',
        'tax_total',
        'commission_amount',
        'status',
        'payment_status',
        'delivery_status',
        'order_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sub_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'status' => 'string',
            'payment_status' => 'string',
            'delivery_status' => 'string',
        ];
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'order_id');
    }

    public function orderVouchers()
    {
        return $this->hasMany(OrderVoucher::class, 'order_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id');
    }
}
