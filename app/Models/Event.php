<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id', 
        'location',
        'start_date',
        'end_date',
        'ticket_price',
        'status',
        'capacity',
        'image'
    ];

    // Define the many-to-one relationship with category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function approvals()
    {
        return $this->hasMany(EventApproval::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

}
