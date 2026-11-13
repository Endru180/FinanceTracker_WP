<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'account_name',
        'account_type',
        'balance',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * `balance` di kolom ini adalah saldo AWAL (opening balance).
     * Saldo yang ditampilkan ke user selalu dihitung ulang dari sini + transaksi,
     * sesuai keputusan "computed on-the-fly" — bukan disimpan manual.
     */
    public function currentBalance(): float
    {
        $net = $this->transactions()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->selectRaw("SUM(CASE WHEN categories.type = 'Income' THEN transaction_amount ELSE -transaction_amount END) as net")
            ->value('net');

        return (float) $this->balance + (float) ($net ?? 0);
    }
}
