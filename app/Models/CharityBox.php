<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharityBox extends Model
{
    use HasFactory;
    // اجازه ذخیره کد، نوع و وضعیت صندوق در دیتابیس
    protected $fillable = [
        'code',
        'type',
        'status'
    ];

    public function allocations()
    {
        return $this->hasMany(BoxAllocation::class, 'box_id');
    }
}
