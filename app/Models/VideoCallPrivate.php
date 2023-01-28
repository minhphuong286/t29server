<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoCallPrivate extends Model
{
    use HasFactory;

    protected $table = 'video_call_privates';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'signal',
        'user_id',
        'private_room_id',
        'created_at',
        'updated_at',
    ];

    const SIGNAL_INCOMING = 0;
    const SIGNAL_CALLING = 1;
    const SIGNAL_CLOSING = 2;
}
