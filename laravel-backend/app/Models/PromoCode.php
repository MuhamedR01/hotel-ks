<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value',
        'min_subtotal', 'starts_at', 'expires_at',
        'max_uses', 'times_used', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_subtotal'   => 'decimal:2',
            'starts_at'      => 'datetime',
            'expires_at'     => 'datetime',
            'is_active'      => 'boolean',
            'max_uses'       => 'integer',
            'times_used'     => 'integer',
        ];
    }

    /**
     * Check whether the code can be redeemed right now for the given subtotal.
     */
    public function isUsable(?float $subtotal = null): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        if ($this->max_uses !== null && $this->times_used >= $this->max_uses) return false;
        if ($subtotal !== null && $this->min_subtotal && $subtotal < (float) $this->min_subtotal) return false;
        return true;
    }

    /**
     * Reason string when not usable. Used for nicer error messages.
     */
    public function reasonNotUsable(?float $subtotal = null): ?string
    {
        if (!$this->is_active) return 'Ky kod është çaktivizuar.';
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return 'Ky kod ende nuk është aktiv.';
        if ($this->expires_at && $now->gt($this->expires_at)) return 'Ky kod ka skaduar.';
        if ($this->max_uses !== null && $this->times_used >= $this->max_uses) return 'Ky kod ka arritur kufirin maksimal të përdorimeve.';
        if ($subtotal !== null && $this->min_subtotal && $subtotal < (float) $this->min_subtotal) {
            return 'Shuma minimale e shportës për këtë kod është ' . number_format((float) $this->min_subtotal, 2) . '€.';
        }
        return null;
    }
}
