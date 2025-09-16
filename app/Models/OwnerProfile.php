<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tax_number',
        'business_name',
        'business_address',
        'business_phone',
        'business_email',
        'business_website',
        'business_description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
