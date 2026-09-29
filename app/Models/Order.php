<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'notes',
        'total_amount',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Get the items for this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate an invoice order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'INV-'.date('Ymd');
        $lastOrder = static::where('order_number', 'like', "{$prefix}-%")
            ->latest('id')
            ->first();

        if (! $lastOrder) {
            return "{$prefix}-0001";
        }

        $lastNumber = (int) substr($lastOrder->order_number, -4);
        $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$nextNumber}";
    }
}
