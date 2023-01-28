<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPrivate extends Model
{
    use HasFactory;

    protected $table = 'notification_privates';
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
        'private_room_id',
        'user_id',
        'read_at',
        'created_at',
        'updated_at',
    ];

    public function lastMessage(){
        return $this->hasOne(PrivateConservation::class,'private_room_id','private_room_id')->latest();
    }

    public function getStatusAttribute()
    {
        $date = PrivateConservation::where('private_room_id',$this->private_room_id)->orderBy('created_at','desc')->first();
        if(!isset($date)){
            return true;
        }
        $date = $date->created_at;
        // return $date;
        $noti = NotificationPrivate::where('private_room_id',$this->private_room_id)
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
