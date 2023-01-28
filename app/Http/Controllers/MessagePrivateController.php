<?php

namespace App\Http\Controllers;

use App\Events\MessagePresenceGroup;
use App\Events\MessagePrivate;
use App\Events\NotificationEvent;
use App\Models\CallUserAcceptions;
use App\Models\NotificationPrivate;
use App\Models\PrivateConservation;
use App\Models\PrivateRoom;
use App\Models\User;
use App\Models\VideoCallPrivate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessagePrivateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createRoom(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'phone' => 'string|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $user = User::where('phone',$request->phone)->first();
        $auth = Auth::user();
        if(!$user){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        if($auth->id == $user->id){
            return $this->errorResponse($this->getMessageError("ERROR_179"),400,"ERROR_179");
        }
        $room = PrivateRoom::where('user_one',$auth->id)->where('user_two',$user->id)->first();
        if($room){
            $checkNoti = NotificationPrivate::where('user_id',$auth->id)
                        ->where('private_room_id',$room->id)
                        ->first();
            if(!$checkNoti){
                $notification_one = NotificationPrivate::create([
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'private_room_id' => $room->id,
                    'user_id' => $auth->id
                ]);
                $notification_two = NotificationPrivate::create([
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'private_room_id' => $room->id,
                    'user_id' => $user->id
                ]);
            }
            return $this->successResponse($room);
        }
        $room_reverse = PrivateRoom::where('user_one',$user->id)->where('user_two',$auth->id)->first();
        if($room_reverse){
            $checkNoti = NotificationPrivate::where('user_id',$auth->id)
                        ->where('private_room_id',$room_reverse->id)
                        ->first();
            if(!$checkNoti){
                $notification_one = NotificationPrivate::create([
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'private_room_id' => $room_reverse->id,
                    'user_id' => $auth->id
                ]);
                $notification_two = NotificationPrivate::create([
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'private_room_id' => $room_reverse->id,
                    'user_id' => $user->id
                ]);
            }
            return $this->successResponse($room_reverse);
        }
        $newRoom = PrivateRoom::create([
            'user_one' => $auth->id,
            'user_two' => $user->id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
        $notification_one = NotificationPrivate::create([
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'private_room_id' => $newRoom->id,
            'user_id' => $auth->id
        ]);
        $notification_two = NotificationPrivate::create([
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'private_room_id' => $newRoom->id,
            'user_id' => $user->id
        ]);
        return $this->successResponse($newRoom);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$private_room_id)
    {
        if(PrivateRoom::where('id',$request->private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $validator = Validator::make($request->all(),[
            'message' => 'string',
            'image' => 'mimes:jpeg,jpg,png,webp,svg',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        if(strlen(trim($request->message)) == 0  && $request->file('image') == null){
            return $this->errorResponse($this->getMessageError("ERROR_180"),400,"ERROR_180");
        }
        $user = Auth::user();
        if($request->hasFile('image')){
            $image = $request->file('image');
            $name_image = time() .'_'.$image->getClientOriginalName();
            $path = 'images_chat/private_'.$private_room_id.'/'.$name_image;
            DB::beginTransaction();
            try{
                $message = PrivateConservation::create([
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'private_room_id' => $private_room_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'image' => $path,
                ]);
                NotificationPrivate::where('private_room_id',$private_room_id)
                ->where('user_id',$user->id)->update([
                    'read_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $destinationPath = public_path().'/images_chat/private_'.$private_room_id.'';
                $image->move($destinationPath,$name_image);
                $getUpdateNoti = NotificationPrivate::where('private_room_id',$private_room_id)
                ->where('user_id','<>',$user->id)->first();
                broadcast(new MessagePrivate($user, $message))->toOthers();
                broadcast(new NotificationEvent($user,$message,$getUpdateNoti->read_at))->toOthers();
                DB::commit();
                return $this->successResponse($this->getMessageNoti('NOTI_116'));
            }
            catch(\Exception $e){
                DB::rollBack();
                return $this->errorResponse($this->getMessageError("ERROR_193"),400,"ERROR_193");
            }

        }
        else{
            DB::beginTransaction();
            try{
                $message = PrivateConservation::create([
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'private_room_id' => $private_room_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                NotificationPrivate::where('private_room_id',$private_room_id)
                        ->where('user_id',$user->id)->update([
                            'read_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                
                $getUpdateNoti = NotificationPrivate::where('private_room_id',$private_room_id)
                        ->where('user_id','<>',$user->id)->first();
                broadcast(new MessagePrivate($user, $message))->toOthers();
                broadcast(new NotificationEvent($user,$message,$getUpdateNoti->read_at))->toOthers();
                DB::commit();
                return $this->successResponse($this->getMessageNoti('NOTI_116'));
            }
            catch(\Exception $e){
                DB::rollBack();
                return $this->errorResponse($this->getMessageError("ERROR_193"),400,"ERROR_193");
            }
        }
    }

    public function seenMessage($private_room_id){
        if(PrivateRoom::where('id',$private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        NotificationPrivate::where('private_room_id',$private_room_id)
            ->where('user_id',Auth::id())
            ->update([
                'read_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ]);
        return $this->successResponse($this->getMessageNoti('NOTI_129'));
        
    }

    public function showPrivateRooms(){
        $user = Auth::user();
        $listRoom = PrivateRoom::where('user_one',$user->id)->orWhere('user_two',$user->id)->pluck('id');
        $getNoti = NotificationPrivate::with('lastMessage')->where('user_id',$user->id)->whereIn('private_room_id',$listRoom)->get()->sortByDesc('lastMessage.created_at');
        return $this->successResponse($getNoti);
    }

    public function call($private_room_id){
        if(PrivateRoom::where('id',$private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallPrivate::where('user_id',$user->id)->where('private_room_id',$private_room_id)->first();
        $checkMember = PrivateRoom::where('id',$private_room_id)
                ->where(function($query) use($user){
                    $query->where('user_one',$user->id)
                        ->orWhere('user_two',$user->id);
                })->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
        }
        if(!isset($checkVideoCall)){
            $message = VideoCallPrivate::create([
                'signal' => VideoCallPrivate::SIGNAL_INCOMING,
                'user_id' => $user->id,
                'private_room_id' => $private_room_id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            if(!$message){
                return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
            }
            broadcast(new MessagePrivate($user, $message))->toOthers();
            return $this->successResponse($this->getMessageNoti('NOTI_121'));
        }
        $checkCallElse = VideoCallPrivate::where('private_room_id',$private_room_id)->where('id','!=',$checkVideoCall->id)->first();
        if(isset($checkCallElse)){
            if($checkCallElse->signal != VideoCallPrivate::SIGNAL_CLOSING){
                return $this->errorResponse($this->getMessageError("ERROR_201"),400,"ERROR_201");
            }
        }
        if($checkVideoCall->signal == VideoCallPrivate::SIGNAL_CLOSING){
            VideoCallPrivate::where('id',$checkVideoCall->id)->update([
                'signal' => VideoCallPrivate::SIGNAL_INCOMING,
                'updated_at' => date("Y-m-d H:i:s"),
        ]);
        $message = VideoCallPrivate::where('user_id',$user->id)->where('private_room_id',$private_room_id)->first();
        broadcast(new MessagePrivate($user, $message))->toOthers();
        return $this->successResponse($this->getMessageNoti('NOTI_121'));
        }
        return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
    }

    public function callAccept($private_room_id){
        if(PrivateRoom::where('id',$private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallPrivate::where('private_room_id',$private_room_id)->where('signal','=',VideoCallPrivate::SIGNAL_INCOMING)->first();

        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
        }
        $checkMember = PrivateRoom::where('id',$private_room_id)
                ->where(function($query) use($user){
                    $query->where('user_one',$user->id)
                        ->orWhere('user_two',$user->id);
                })->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
        }
        if($checkVideoCall->signal == VideoCallPrivate::SIGNAL_CLOSING ){
            return $this->errorResponse($this->getMessageError("ERROR_194"),400,"ERROR_194");
        }
        $whoCall = CallUserAcceptions::where('user_id',$checkVideoCall->user_id)->where('vcp_id',$checkVideoCall->id)->first();
        $isCall = CallUserAcceptions::where('user_id',$user->id)->where('vcp_id',$checkVideoCall->id)->first();
        DB::beginTransaction();
        try {
            if(!$isCall){
                $message = CallUserAcceptions::create([
                    'user_id' => $user->id,
                    'vcp_id' => $checkVideoCall->id,
                    'signal' => CallUserAcceptions::CALL_ACCEPTED,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                if(!isset($message)){
                    throw new Exception($this->getMessageError("ERROR_192"));
                }
                $message->private_room_id = $checkVideoCall->private_room_id;
                $call=  json_decode($message);
            }
            else{
                if($isCall->signal == CallUserAcceptions::CALL_ACCEPTED){
                    return $this->errorResponse($this->getMessageError("ERROR_175"),400,"ERROR_175");
                }
                CallUserAcceptions::where('id',$isCall->id)->update([
                    'signal'=> CallUserAcceptions::CALL_ACCEPTED,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $message =  CallUserAcceptions::where('id',$isCall->id)->first();
                if(!isset($message)){
                    return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
                }
                $message->private_room_id = $checkVideoCall->private_room_id;
                $call=  json_decode($message);
            }
            if(!isset($whoCall)){
                $userCalling = CallUserAcceptions::create([
                    'user_id' => $checkVideoCall->user_id,
                    'vcp_id' => $checkVideoCall->id,
                    'signal' => CallUserAcceptions::CALL_ACCEPTED,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                if(!isset($userCalling)){
                    throw new Exception("Người gọi không thể vào được phòng call !");
                }
            }
            else{
                if($whoCall->signal == CallUserAcceptions::CALL_REJECT){
                    CallUserAcceptions::where('id',$whoCall->id)->update([
                        'signal' => CallUserAcceptions::CALL_ACCEPTED,
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                    $checkUserCalling = CallUserAcceptions::where('id',$whoCall->id)->where('signal',CallUserAcceptions::CALL_ACCEPTED)->first();
                    if(!isset($checkUserCalling)){
                        throw new Exception("Người gọi không thể vào được phòng call !");
                    }
                }
            }
            if($checkVideoCall->signal == VideoCallPrivate::SIGNAL_INCOMING){
                VideoCallPrivate::where('id',$checkVideoCall->id)->update([
                    'signal' => VideoCallPrivate::SIGNAL_CALLING,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }
            $checkCalling =  VideoCallPrivate::where('id',$checkVideoCall->id)->where('signal',VideoCallPrivate::SIGNAL_CALLING)->first();
            if(!isset($checkCalling)){
                throw new Exception('Video call vẫn chưa được bắt đầu gọi !');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($this->getMessageError($e->getMessage()),400,"ERROR_300");
        }
        broadcast(new MessagePrivate($user, $call))->toOthers();
        return $this->successResponse($this->getMessageNoti('NOTI_122'));
    }

    public function rejectCall($private_room_id){
        if(PrivateRoom::where('id',$private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallPrivate::where('private_room_id',$private_room_id)->where('signal','=',VideoCallPrivate::SIGNAL_INCOMING)->first();
        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
        }
        $checkMember = PrivateRoom::where('id',$private_room_id)
                    ->where(function($query) use($user){
                        $query->where('user_one',$user->id)
                            ->orWhere('user_two',$user->id);
                    })->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
        }
        DB::beginTransaction();
        try {
            if($checkVideoCall->signal == VideoCallPrivate::SIGNAL_INCOMING){
                $whoCall = CallUserAcceptions::where('user_id',$checkVideoCall->user_id)->where('vcp_id',$checkVideoCall->id)->first();
                $isCall = CallUserAcceptions::where('user_id',$user->id)->where('vcp_id',$checkVideoCall->id)->first();
                if(!$whoCall || !$isCall){
                    CallUserAcceptions::create([
                        'signal' => CallUserAcceptions::CALL_REJECT,
                        'user_id' => $user->id,
                        'vcp_id' => $checkVideoCall->id,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                }
                else{
                    CallUserAcceptions::where('vcp_id',$checkVideoCall->id)->update([
                        'signal' => CallUserAcceptions::CALL_REJECT,
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                }
                VideoCallPrivate::where('id',$checkVideoCall->id)->update([
                    'signal' => VideoCallPrivate::SIGNAL_CLOSING,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $isCallNew = CallUserAcceptions::where('user_id',$user->id)->where('vcp_id',$checkVideoCall->id)->first();
                $isCallNew->private_room_id = $checkVideoCall->private_room_id;
                $call=  json_decode($isCallNew);
                broadcast(new MessagePrivate($user, $call))->toOthers();
            }
            else{
                throw new Exception('Cuộc gọi vẫn chưa diễn ra hoặc đang trong quá trình gọi!');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($this->getMessageError($e->getMessage()),400,"ERROR_301");
        }
        return $this->successResponse($this->getMessageNoti('NOTI_127'));

    }

    public function closeCall($private_room_id){
        if(PrivateRoom::where('id',$private_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallPrivate::where('private_room_id',$private_room_id)->where('signal','=',VideoCallPrivate::SIGNAL_CALLING)->first();

        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
        }
        $checkMember = PrivateRoom::where('id',$private_room_id)
                    ->where(function($query) use($user){
                        $query->where('user_one',$user->id)
                            ->orWhere('user_two',$user->id);
                    })->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
        }
        if($checkVideoCall->signal != VideoCallPrivate::SIGNAL_CALLING ){
            return $this->errorResponse($this->getMessageError("ERROR_200"),400,"ERROR_200");
        }
        $whoCall = CallUserAcceptions::where('user_id',$checkVideoCall->user_id)->where('vcp_id',$checkVideoCall->id)->first();
        $isCall = CallUserAcceptions::where('user_id',$user->id)->where('vcp_id',$checkVideoCall->id)->first();
        if(!$whoCall || !$isCall){
            return $this->errorResponse($this->getMessageError("ERROR_200"),400,"ERROR_200");
        }
        DB::beginTransaction();
        try {
            if($checkVideoCall->signal == VideoCallPrivate::SIGNAL_CALLING){
                CallUserAcceptions::where('vcp_id',$checkVideoCall->id)->update([
                    'signal' => CallUserAcceptions::CALL_REJECT,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                VideoCallPrivate::where('id',$checkVideoCall->id)->update([
                    'signal' => VideoCallPrivate::SIGNAL_CLOSING,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $isCallNew = CallUserAcceptions::where('user_id',$user->id)->where('vcp_id',$checkVideoCall->id)->first();
                $isCallNew->private_room_id = $checkVideoCall->private_room_id;
                $call=  json_decode($isCallNew);
                broadcast(new MessagePrivate($user, $call))->toOthers();
            }
            else{
                throw new Exception('Cuộc gọi vẫn chưa diễn ra');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($this->getMessageError($e->getMessage()),400,"ERROR_301");
        }
        return $this->successResponse($this->getMessageNoti('NOTI_125'));
    }

    public function showMessage($private_room_id){
        $checkPresence = PrivateRoom::where('id',$private_room_id)->first();
        if(!$checkPresence){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $messages = PrivateConservation::with('user')->where('private_room_id',$private_room_id)->orderBy('private_conservations.created_at','desc')->paginate(10);
        return $messages;
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
