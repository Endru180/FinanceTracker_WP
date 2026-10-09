<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaction extends Model
{
    protected $fillable = [
        'account_id',
        'category_id',
        'transaction_amount',
        'transaction_date'
    ];

    protected $casts = [
        'transaction_date' => 'datetime'
    ];


    #relationship with accounts
    public function account(){
        return $this->belongsTo(Account::class);
    }

    #relationship with category
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
