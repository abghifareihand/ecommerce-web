<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'tagline', 'logo', 'phone', 'email', 'address', 'bank_account', 'description'])]
class StoreSetting extends Model
{
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['clean_phone', 'logo_url'];

    /**
     * Clean phone number for WhatsApp API (e.g. 0812 -> 62812).
     */
    public function getCleanPhoneAttribute(): string
    {
        $raw = preg_replace('/[^0-9]/', '', $this->phone ?? '');
        if (str_starts_with($raw, '0')) {
            return '62'.substr($raw, 1);
        }

        return $raw ?: '628985454555';
    }

    /**
     * Get the store logo public URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
                return $this->logo;
            }

            if (str_starts_with($this->logo, 'assets/') || str_starts_with($this->logo, '/assets/')) {
                return asset(ltrim($this->logo, '/'));
            }

            if (Storage::disk('public')->exists($this->logo)) {
                return Storage::disk('public')->url($this->logo);
            }
        }

        // Always fallback to default asset image from public/assets/img/logo.png
        if (file_exists(public_path('assets/img/logo.png'))) {
            return asset('assets/img/logo.png');
        }

        return null;
    }
}
