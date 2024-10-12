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
        'event_date',
        'start_date',
        'end_date',
        'ticket_price',
        'status',
        'capacity',
        'imgUrl'
    ];

    // Define the many-to-one relationship with category
    public function category()
    {
        return $this->belongsTo(Categories::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

}
