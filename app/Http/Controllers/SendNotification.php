<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\PrivateConservation;
use App\Models\PrivateRoom;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SendNotification extends Controller
{
    public function sendPrivate($private_room_id){
        $messages = PrivateConservation::where('private_room_id',$private_room_id)
                    ->orderBy('created_at','desc')->first();
        $privateRoom = PrivateRoom::where('id',$private_room_id)->first();
        if($privateRoom->user_one == $messages->user_id){
            $getUser = $privateRoom->user_two;
        }
        else if($privateRoom->user_two == $messages->user_id){
            $getUser = $privateRoom->user_one;
        }
        
        $user = User::find($getUser);
        $user->notify(new MessageNotification($messages));
        
        return $this->successResponse($this->getMessageNoti('NOTI_128'));

    }
}
