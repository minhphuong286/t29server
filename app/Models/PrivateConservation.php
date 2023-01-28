<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateConservation extends Model
{
    use HasFactory;
    protected $table = 'private_conservations';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'message',
        'user_id',
        'status',
        'private_room_id',
        'created_at',
        'updated_at',
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
