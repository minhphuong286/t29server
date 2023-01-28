<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresenceConservation extends Model
{
    use HasFactory;

    protected $table = 'presence_conservations';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'message',
        'status',
        'user_id',
        'presence_room_id',
        'created_at',
        'updated_at',
        'image'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function room(){
        return $this->belongsTo(PresenceRoom::class,'presence_room_id','id');
    }
}
