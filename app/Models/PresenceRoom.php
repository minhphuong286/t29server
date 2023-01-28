<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresenceRoom extends Model
{
    use HasFactory;
    protected $table = 'presence_rooms';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];

    public function lastMessage(){
        return $this->hasOne(PresenceConservation::class,'presence_room_id','id')->latest();
    }

    public function notification(){
        return $this->hasOne(NotificationPresence::class,'presence_room_id','id');
    }
}
