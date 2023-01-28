<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelationship extends Model
{
    use HasFactory;

    protected $table = 'user_relationships';
    public $timestamps = false;

    const REQUEST_STATUS = 'requested';
    const DELETE_STATUS = 'deleted';
    const FRIEND_STATUS = 'friend';

    const MESSAGE_REQUEST = 'request-message';
    const MESSAGE_DELETE = 'delete-message';

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

    public function userOne(){
        return $this->hasOne(User::class,'id','user_one');
    }

    public function userTwo(){
        return $this->hasOne(User::class,'id','user_two');
    }
}
