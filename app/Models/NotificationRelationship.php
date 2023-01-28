<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRelationship extends Model
{
    use HasFactory;
    protected $table = 'notification_relationships';
    public $timestamps = false;

    const DELETE_STATUS = 'deleted';
    const REQUEST_STATUS = 'requested';
    const FRIEND_STATUS = 'friend';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'status',
        'message',
        'relationship_id',
        'created_at',
        'updated_at',
    ];

    public function relationship(){
        return $this->hasMany(UserRelationship::class,'id','relationship_id');
    }
}
