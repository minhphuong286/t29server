<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPresence extends Model
{
    use HasFactory;

    protected $table = 'notification_presences';
    protected $appends = ['status'];
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'status',
        'message',
        'presence_room_id',
        'user_id',
        'read_at',
        'created_at',
        'updated_at',
    ];

    public function getStatusAttribute()
    {
        $date = PresenceConservation::where('presence_room_id',$this->presence_room_id)->orderBy('created_at','desc')->first();
        if(!isset($date)){
            return true;
        }
        $date = $date->created_at;
        // return $date;
        $noti = NotificationPresence::where('presence_room_id',$this->presence_room_id)
                ->where('user_id',$this->user_id)
                ->orderBy('updated_at','desc')
                ->first();
        if(!isset($noti)){
            return true;
        }
        $readAt = $noti->read_at;
        if($date >= $readAt){
            return false;
        }
        return true;
    }

}
