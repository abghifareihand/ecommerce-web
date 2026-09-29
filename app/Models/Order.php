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
        'shipping_cost',
        'courier',
        'tracking_number',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['grand_total', 'status_label'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
        ];
    }

    /**
     * Calculate grand total including shipping cost.
     */
    public function getGrandTotalAttribute(): float
    {
        return (float) ($this->total_amount ?? 0) + (float) ($this->shipping_cost ?? 0);
    }

    /**
     * Get user-friendly Indonesian status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending (Cek Ongkir)',
            'payment_pending' => 'Menunggu Pembayaran',
            'processing', 'confirmed' => 'Diproses & Dikemas',
            'shipped' => 'Sedang Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
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
