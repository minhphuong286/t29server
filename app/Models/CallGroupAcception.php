<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallGroupAcception extends Model
{
    use HasFactory;

    protected $table = 'call_group_acceptions';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'signal',
        'user_id',
        'vcg_id',
        'created_at',
        'updated_at',
    ];

    const CALL_ACCEPTED = 0;
    const CALL_REJECT = 1;
}
