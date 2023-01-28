<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateRoom extends Model
{
    use HasFactory;

    protected $table = 'private_rooms';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_one',
        'user_two',
        'status',
        'created_at',
        'updated_at',
    ];

    
    public function lastMessage(){
        return $this->hasOne(PrivateConservation::class,'private_room_id','id')->latest();
    }
}
