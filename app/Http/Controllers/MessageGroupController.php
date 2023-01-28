<?php

namespace App\Http\Controllers;

use App\Events\MessagePresenceGroup;
use App\Events\NotificationPresenece;
use App\Models\CallGroupAcception;
use App\Models\NotificationPresence;
use App\Models\Participant;
use App\Models\PresenceConservation;
use App\Models\PresenceRoom;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\VideoCallGroup;
use Egulias\EmailValidator\Parser\PartParser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class MessageGroupController extends Controller
{
    public function index()
    {
        // if ($messages = Redis::get('messages.all')) {
        //     return json_decode($messages);
        // }
        $messages = PresenceConservation::with('user')->get();
        Redis::set('messages.all', $messages);

        return $this->successResponse($messages);
    }

    public function store(Request $request,$presence_room_id)
    {   
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
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
            $path = 'images_chat/group_'.$presence_room_id.'/'.$name_image;
            DB::beginTransaction();
            try{
                $message = PresenceConservation::create([
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'presence_room_id' => $presence_room_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                    'image' => $path,
                ]);
                $checkNoti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->first();
                if(!$checkNoti){
                    $noti = NotificationPresence::create([
                        'presence_room_id' => $presence_room_id,
                        'read_at' => date("Y-m-d H:i:s"),
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                        'user_id' => $user->id,
                    ]);
                }
                else{
                    NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->update([
                            'read_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                    
                    $noti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->first();
                }
                $destinationPath = public_path().'/images_chat/group_'.$presence_room_id.'';
                $image->move($destinationPath,$name_image);
                broadcast(new MessagePresenceGroup($user, $message));
                broadcast(new NotificationPresenece($user,$message,$noti->read_at));
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
                $message = PresenceConservation::create([
                    'user_id' => $user->id,
                    'message' => $request->message,
                    'presence_room_id' => $presence_room_id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $checkNoti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->first();
                if(!$checkNoti){
                    $noti = NotificationPresence::create([
                        'presence_room_id' => $presence_room_id,
                        'read_at' => date("Y-m-d H:i:s"),
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                        'user_id' => $user->id,
                    ]);
                }
                else{
                    NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->update([
                            'read_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                    
                    $noti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->first();
                }
                broadcast(new MessagePresenceGroup($user, $message));
                broadcast(new NotificationPresenece($user,$message,$noti->read_at));
                DB::commit();
                return $this->successResponse($this->getMessageNoti('NOTI_116'));
            }
            catch(\Exception $e){
                DB::rollBack();
                return $this->errorResponse($this->getMessageError("ERROR_193"),400,"ERROR_193");
            }
        }
        
    }

    public function seenMessage($presence_room_id){
        $user = Auth::user();
        $room = PresenceRoom::where('id',$presence_room_id)->first();
        if(!$room){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $checkNoti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                        ->where('user_id',$user->id)
                        ->first();
        if(!$checkNoti){
            $noti = NotificationPresence::create([
                'presence_room_id' => $presence_room_id,
                'read_at' => date("Y-m-d H:i:s"),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'user_id' => $user->id,
            ]);
        }
        else{
            NotificationPresence::where( 'presence_room_id',$presence_room_id)
                ->where('user_id',$user->id)
                ->update([
                    'read_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            
            $noti = NotificationPresence::where( 'presence_room_id',$presence_room_id)
                ->where('user_id',$user->id)
                ->first();
        }

        return $this->successResponse($noti);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'string|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $user = Auth::user();
   
        $room = PresenceRoom::create([
            'name' => $request->name,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
        $participant = Participant::create([
            'user_id' => $user->id,
            'presence_room_id' => $room->id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'role' => 1,
        ]);
        
        $noti = NotificationPresence::create([
            'presence_room_id' => $room->id,
            'read_at' => date("Y-m-d H:i:s"),
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
            'user_id' => $user->id,
        ]);
        return $this->successResponse($this->getMessageNoti('NOTI_110'));
       
       
    }

    public function findUser(Request $request){
        $userOne = Auth::user();

        $user = User::where('phone',$request->search)
                ->orWhere('name','like','%'.$request->search.'%')
                ->get();
        for ($i=0; $i < count($user) ; $i++) { 
            $userTwo = $user[$i];
            $checkFriend = UserRelationship::where(function($query) use($userOne){
                $query->where('user_one',$userOne->id)
                    ->orWhere('user_two',$userOne->id);
                })
                ->where(function($query) use($userTwo){
                    $query->where('user_one',$userTwo->id)
                        ->orWhere('user_two',$userTwo->id);
                })
                ->where('status','friend')
                ->first();
            if($userOne->id == $userTwo->id){
                $isFriend = 2;
            }
            else{
                if($checkFriend){
                    $isFriend = 1;
                }
                else{
                    $isFriend = 0;
                }
            }
            $user[$i]->isFriend = $isFriend;
        }
        return $this->successResponse($user);
    }

    public function getDetailUser($phone){
        $userOne = Auth::user();
        $userTwo = User::where('phone',$phone)->first();
        if($userTwo == null){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        
        $checkFriend = UserRelationship::where(function($query) use($userOne){
            $query->where('user_one',$userOne->id)
                ->orWhere('user_two',$userOne->id);
            })
            ->where(function($query) use($userTwo){
                $query->where('user_one',$userTwo->id)
                    ->orWhere('user_two',$userTwo->id);
            })
            ->where('status','friend')
            ->first();
        if($userOne->id == $userTwo->id){
            $isFriend = 2;
        }   
        else{
            if($checkFriend){
            $isFriend = 1;
            }
            else{
                $isFriend = 0;
            }
        }
        
        $user = User::where('phone',$phone)->first();
        $user->isFriend = $isFriend;
        return $this->successResponse($user);
    }

    public function getDetailRoom($room_id){
        if(PresenceRoom::where('id',$room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $room = PresenceRoom::where('id',$room_id)->first();
        return $this->successResponse($room);
    }


    public function showPresenceRooms(){
        $user = Auth::user();
        $getParticipant = Participant::where('user_id',$user->id)->pluck('presence_room_id');
        $presenceRoom = PresenceRoom::with(['lastMessage','notification' => function($query){
            $query->where('user_id',Auth::user()->id);
        }])->whereIn('id',$getParticipant)->get();
        
        return $this->successResponse($presenceRoom);
    }

    public function addMember(Request $request){
        $validator = Validator::make($request->all(),[
            'presence_room_id' => 'int|required',
            'phone' => 'string|required'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $user =  $user = User::where('phone',$request->phone)->first();
        if($user == null){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        if(PresenceRoom::where('id',$request->presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $checkUser = Participant::where('presence_room_id',$request->presence_room_id)->where('user_id',$user->id)->first();
        if($checkUser != null){
            return $this->errorResponse($this->getMessageError("ERROR_177"),400,"ERROR_177");
        }
        Participant::create([
            'user_id' => $user->id,
            'presence_room_id' => $request->presence_room_id,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
        return $this->successResponse(['message'=> $this->getMessageNoti('NOTI_111'),'user' => $user]);
    }


    public function showMessage($presence_room_id){
        $checkPresence = PresenceRoom::where('id',$presence_room_id)->first();
        if(!$checkPresence){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user_id = Auth::user()->id;
        $noti = NotificationPresence::where('presence_room_id',$presence_room_id)->where('user_id',$user_id)->first();
        if(!$noti){
            $noti = NotificationPresence::create([
                'presence_room_id' => $presence_room_id,
                'read_at' => date("Y-m-d H:i:s"),
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
                'user_id' => $user_id,
            ]);
        }
        $messages = PresenceConservation::with('user','room')->where('presence_room_id',$presence_room_id)->orderBy('presence_conservations.created_at','desc')->paginate(10);
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

    public function callGroup ($presence_room_id){
        
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallGroup::where('user_id',$user->id)->where('presence_room_id',$presence_room_id)->first();
        if(!$checkVideoCall){
            $message = VideoCallGroup::create([
                'signal' => VideoCallGroup::SIGNAL_INCOMING,
                'presence_room_id' => $presence_room_id,
                'user_id' => $user->id,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            if(!$message){
                return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
            }
            broadcast(new MessagePresenceGroup($user, $message))->toOthers();
            return $this->successResponse($this->getMessageNoti('NOTI_121'));
        }
        $checkCallElse = VideoCallGroup::where('presence_room_id',$presence_room_id)->where('id','!=',$checkVideoCall->id)->first();
        if(isset($checkCallElse)){
            if($checkCallElse->signal != VideoCallGroup::SIGNAL_CLOSING){
                return $this->errorResponse($this->getMessageError("ERROR_201"),400,"ERROR_201");
            }
        }
        if($checkVideoCall->signal == VideoCallGroup::SIGNAL_CLOSING){
            VideoCallGroup::where('id',$checkVideoCall->id)->update([
                    'signal' => VideoCallGroup::SIGNAL_INCOMING,
                    'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $message = VideoCallGroup::where('user_id',$user->id)->where('presence_room_id',$presence_room_id)->first();
            broadcast(new MessagePresenceGroup($user, $message))->toOthers();
            return $this->successResponse($this->getMessageNoti('NOTI_121'));
        }
        return $this->errorResponse($this->getMessageError("ERROR_191"),400,"ERROR_191");
    }

    public function acceptionCall($vcg_id){
        $user = Auth::user();
        $checkVideoCall = VideoCallGroup::where('id',$vcg_id)->first();
        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
        }
        if($checkVideoCall->signal == VideoCallGroup::SIGNAL_CLOSING ){
            return $this->errorResponse($this->getMessageError("ERROR_194"),400,"ERROR_194");
        }
        $checkMember = Participant::where('user_id',$user->id)->where('presence_room_id',$checkVideoCall->presence_room_id)->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_172"),400,"ERROR_172");
        }
        $checkCallGroup = CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->first();
        $whoCall = CallGroupAcception::where('user_id',$checkVideoCall->user_id)->where('vcg_id',$checkVideoCall->id)->first();
        DB::beginTransaction();
        try {
            if(!isset($checkCallGroup)){
                $message = CallGroupAcception::create([
                    'user_id' => $user->id,
                    'vcg_id' => $vcg_id,
                    'signal' => CallGroupAcception::CALL_ACCEPTED,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                if(!isset($message)){
                    throw new Exception($this->getMessageError("ERROR_192"));
                }
                $message->presence_room_id = $checkVideoCall->presence_room_id;
                $call=  json_decode($message);
            }
            else{
                if($checkCallGroup->signal == CallGroupAcception::CALL_ACCEPTED){
                    return $this->errorResponse($this->getMessageError("ERROR_175"),400,"ERROR_175");
                }
                CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->update([
                    'signal' => CallGroupAcception::CALL_ACCEPTED,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $message = CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->first();
                if(!isset($message)){
                    return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
                }
                $message->presence_room_id = $checkVideoCall->presence_room_id;
                $call=  json_decode($message);
               
            }
            if(!isset($whoCall)){
                $userCalling = CallGroupAcception::create([
                    'user_id' => $checkVideoCall->user_id,
                    'vcg_id' => $vcg_id,
                    'signal' => CallGroupAcception::CALL_ACCEPTED,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                if(!isset($userCalling)){
                    throw new Exception("Người gọi không thể vào được phòng call !");
                }
            }
            else{
                if($whoCall->signal == CallGroupAcception::CALL_REJECT){
                    CallGroupAcception::where('id',$whoCall->id)->update([
                        'signal' => CallGroupAcception::CALL_ACCEPTED,
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                    $checkUserCalling = CallGroupAcception::where('id',$whoCall->id)->where('signal',CallGroupAcception::CALL_ACCEPTED)->first();
                    if(!isset($checkUserCalling)){
                        throw new Exception("Người gọi không thể vào được phòng call !");
                    }
                }
            }
            if($checkVideoCall->signal == VideoCallGroup::SIGNAL_INCOMING){
                VideoCallGroup::where('id',$vcg_id)->update([
                    'signal' => VideoCallGroup::SIGNAL_CALLING,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
            }
            $checkCalling =  VideoCallGroup::where('id',$vcg_id)->where('signal',VideoCallGroup::SIGNAL_CALLING)->first();
            if(!isset($checkCalling)){
                throw new Exception('Video call vẫn chưa được bắt đầu gọi !');
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($this->getMessageError($e->getMessage()),400,"ERROR_300");
        }
        broadcast(new MessagePresenceGroup($user, $call))->toOthers();
        return $this->successResponse($this->getMessageNoti('NOTI_122'));
    }


    public function rejectCall(Request $request){
        $validator = Validator::make($request->all(),[
            'vcg_id' => 'int|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallGroup::where('id',$request->vcg_id)->first();
        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_171"),400,"ERROR_171");
        }
        if($checkVideoCall->signal == VideoCallGroup::SIGNAL_CLOSING){
            return $this->errorResponse($this->getMessageError("ERROR_200"),400,"ERROR_200");
        }
        $checkUserInRoom = CallGroupAcception::where('vcg_id',$request->vcg_id)
                ->where('signal',CallGroupAcception::CALL_ACCEPTED)
                ->first();
        if(isset($checkUserInRoom)){
            return $this->errorResponse($this->getMessageError("ERROR_173"),400,"ERROR_173");
        }
        VideoCallGroup::where('id',$checkVideoCall->id)->update([
            'signal' => VideoCallGroup::SIGNAL_CLOSING,
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
        $call = VideoCallGroup::where('id',$checkVideoCall->id)->first();
        broadcast(new MessagePresenceGroup($user, $call))->toOthers();
        return $this->successResponse($this->getMessageNoti('NOTI_125'));
    }

    public function closeCall($vcg_id){
        $user = Auth::user();
        $checkVideoCall = VideoCallGroup::where('id',$vcg_id)->first();
        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_192"),400,"ERROR_192");
        }
        if($checkVideoCall->signal == VideoCallGroup::SIGNAL_CLOSING){
            return $this->errorResponse($this->getMessageError("ERROR_194"),400,"ERROR_194");
        }
        $checkMember = Participant::where('user_id',$user->id)->where('presence_room_id',$checkVideoCall->presence_room_id)->first();
        if(!isset($checkMember)){
            return $this->errorResponse($this->getMessageError("ERROR_172"),400,"ERROR_172");
        }
        $checkCallGroup = CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->first();
        DB::beginTransaction();
        try {
            if(!isset($checkCallGroup)){
                $message = CallGroupAcception::create([
                    'user_id' => $user->id,
                    'vcg_id' => $vcg_id,
                    'signal' => CallGroupAcception::CALL_REJECT,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $message->presence_room_id = $checkVideoCall->presence_room_id;
                $call=  json_decode($message);
                $participation = false;
            }
            else{
                if($checkCallGroup->signal == CallGroupAcception::CALL_ACCEPTED){
                    $participation = true;
                    CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->update([
                        'signal' => CallGroupAcception::CALL_REJECT,
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                    $message = CallGroupAcception::where('user_id',$user->id)->where('vcg_id',$vcg_id)->first();
                    $message->presence_room_id = $checkVideoCall->presence_room_id;
                    $call=  json_decode($message);
                }
                else{
                    $participation = false;
                }      
            }
            $checkLastUser = CallGroupAcception::where('signal',CallGroupAcception::CALL_ACCEPTED)->where('vcg_id',$vcg_id)->first();
            if(!isset($checkLastUser)){
                VideoCallGroup::where('id',$vcg_id)->update([
                    'signal' => VideoCallGroup::SIGNAL_CLOSING,
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);
                $getClosingCall = VideoCallGroup::where('id',$vcg_id)->where('signal',VideoCallGroup::SIGNAL_CLOSING)->first();
            }
            DB::commit();
            broadcast(new MessagePresenceGroup($user, $call))->toOthers();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($this->getMessageError($e->getMessage()),400,"ERROR_301");
        }
        if(isset($getClosingCall)){
            return $this->successResponse($this->getMessageNoti('NOTI_125'));
        }
        else if($participation == false){
            return $this->successResponse($this->getMessageNoti('NOTI_127'));
        }
        return $this->successResponse($this->getMessageNoti('NOTI_126'));
    }

    public function callingStatus(Request $request){
        $validator = Validator::make($request->all(),[
            'vcg_id' => 'int|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $user = Auth::user();
        $checkVideoCall = VideoCallGroup::where('id',$request->vcg_id)->first();
        if(!isset($checkVideoCall)){
            return $this->errorResponse($this->getMessageError("ERROR_171"),400,"ERROR_171");
        }
        if($checkVideoCall->signal != VideoCallGroup::SIGNAL_INCOMING){
            return $this->errorResponse($this->getMessageError("ERROR_171"),400,"ERROR_171");
        }
        $checkAccepVideoCall = CallGroupAcception::where('vcg_id',$checkVideoCall->id)
                ->where('signal',CallGroupAcception::CALL_ACCEPTED)
                ->first();
        if(isset($checkAccepVideoCall)){
            VideoCallGroup::where('id',$checkVideoCall->id)->update([
                'signal' => VideoCallGroup::SIGNAL_CALLING,
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
            $message = VideoCallGroup::where('id',$request->vcg_id)->first();
            broadcast(new MessagePresenceGroup($user, $message))->toOthers();
            return $this->successResponse($this->getMessageNoti('NOTI_124'));
        }
        VideoCallGroup::where('id',$checkVideoCall->id)->update([
            'signal' => VideoCallGroup::SIGNAL_CLOSING,
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
        $message = VideoCallGroup::where('id',$request->vcg_id)->first();
        broadcast(new MessagePresenceGroup($user, $message))->toOthers();
        return $this->successResponse($this->getMessageNoti('NOTI_123'));
        
    }

    public function kickUser($presence_room_id,$user_id){
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        if(!isset($user_id)){
            return $this->errorResponse($this->getMessageError("ERROR_197"),400,"ERROR_197");
        }
        $userKicked = User::where('id',$user_id)->first();
        if(!$userKicked){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        $user = Auth::user();
        $isMaster = Participant::where('user_id',$user->id)->where('presence_room_id',$presence_room_id)->where('role',1)->first();
        if(!isset($isMaster)){
            return $this->errorResponse($this->getMessageError("ERROR_174"),400,"ERROR_174");
        }
        if($user->id == $user_id){
            return $this->errorResponse($this->getMessageError("ERROR_195"),400,"ERROR_195");
        }
      
        Participant::where('user_id',$user_id)->where('presence_room_id',$presence_room_id)->delete();
        return $this->successResponse("Người dùng ".$userKicked->name." đã bị kick khỏi nhóm");
    }

    public function memberGroup($presence_room_id){
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $member = User::select('users.*','participants.role')
                ->join('participants','participants.user_id','=','users.id')
                ->where('participants.presence_room_id',$presence_room_id)
                ->get();
        return $this->successResponse($member);
    }

    public function countMember($presence_room_id){
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $count = Participant::select(DB::raw('COUNT(participants.id) as num_of_members'))
                ->where('presence_room_id',$presence_room_id)
                ->first();
        return $this->successResponse($count);
    }

    public function outRoom($presence_room_id){
        if(PresenceRoom::where('id',$presence_room_id)->first() == null){
            return $this->errorResponse($this->getMessageError("ERROR_178"),400,"ERROR_178");
        }
        $user = Auth::user();
        $checkAdmin = Participant::where('user_id',$user->id)
                    ->where('role',1)
                    ->where('presence_room_id',$presence_room_id)
                    ->first();
        
        $countAdmin = Participant::select(DB::raw('COUNT(participants.id) as num_of_admins'))
                    ->where('role',1)
                    ->where('presence_room_id',$presence_room_id)
                    ->first();
        
        if(isset($checkAdmin) && $countAdmin->num_of_admins == 1){
            return $this->errorResponse($this->getMessageError("ERROR_196"),400,"ERROR_196");
        }
        else if(isset($checkAdmin) && $countAdmin->num_of_admins >1){
            Participant::where('user_id',$user->id)->where('presence_room_id',$presence_room_id)->where('role',1)->delete();
            return $this->successResponse("Người dùng ".$user->name." đã rời khỏi nhóm");
        }
        Participant::where('user_id',$user->id)->where('presence_room_id',$presence_room_id)->delete();
        return $this->successResponse("Người dùng ".$user->name." đã rời khỏi nhóm");
    }


    public function authorizeUser($presence_room_id,$user_id){
        if(!isset($user_id)){
            return $this->errorResponse($this->getMessageError("ERROR_197"),400,"ERROR_197");
        }
        $user = Auth::user();
        $checkAdmin = Participant::where('user_id',$user->id)
            ->where('role',1)
            ->where('presence_room_id',$presence_room_id)
            ->first();
        $userUp = User::where('id',$user_id)->first();
        if(!isset($checkAdmin)){
            return $this->errorResponse($this->getMessageError("ERROR_198"),400,"ERROR_198");
        }
        if($user->id == $user_id){
            return $this->errorResponse($this->getMessageError("ERROR_199"),400,"ERROR_199");
        }
        Participant::where('user_id',$user_id)->where('presence_room_id',$presence_room_id)->update([
            'role' => 1,
            'updated_at' => date("Y-m-d H:i:s"),
        ]);
        return $this->successResponse($user->name." đã cho ".$userUp->name." trở thành chủ phòng");
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
