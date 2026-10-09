<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'category_name',
        'type'
    ];

    
    # relationship with transaction
    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

}
