<?php

namespace App\Http\Controllers;

use App\Events\NotificationRelationship;
use App\Models\NotificationRelationship as ModelsNotificationRelationship;
use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RelationshipController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendRequest(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'active' => 'boolean|required',
            'user_two' => 'int|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $userOne = Auth::user();
        $userTwo = User::where('id',$request->user_two)->first();
        if(!$userTwo){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        if($userOne->id == $userTwo->id){
            return $this->errorResponse($this->getMessageError("ERROR_186"),400,"ERROR_186");
        }
        $checked = UserRelationship::where(function($query) use($userOne){
                    $query->where('user_one',$userOne->id)
                        ->orWhere('user_two',$userOne->id);
                })
                ->Where(function($query) use($userTwo){
                    $query->where('user_one',$userTwo->id)
                        ->orWhere('user_two',$userTwo->id);
                })
                ->where('status','<>',UserRelationship::FRIEND_STATUS)
                ->first();
        
        if($request->active == 1){
            if(!$checked){
                $checkTwoTime = UserRelationship::where(function($query) use($userOne){
                        $query->where('user_one',$userOne->id)
                            ->orWhere('user_two',$userOne->id);
                    })
                    ->Where(function($query) use($userTwo){
                        $query->where('user_one',$userTwo->id)
                            ->orWhere('user_two',$userTwo->id);
                    })
                    ->first();
                if(!$checkTwoTime){
                    $relation = UserRelationship::create([
                        'user_one' => $userOne->id,
                        'user_two' => $userTwo->id,
                        'status' => 'requested',
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
                else{
                    return $this->errorResponse($this->getMessageError("ERROR_185"),400,"ERROR_185");
                }
                
            }
            else{
                if($checked->status == UserRelationship::DELETE_STATUS){
                    UserRelationship::where('id',$checked->id)->update([
                        'status' => 'requested',
                        'updated_at' => date("Y-m-d H:i:s")
                    ]);
                }
                else{
                    $checkWhoSend = UserRelationship::where('id',$checked->id)
                        ->where('user_one',$userTwo->id)->first();
                    if($checkWhoSend){
                        UserRelationship::where('id',$checked->id)->update([
                            'status' => UserRelationship::FRIEND_STATUS,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                    }
                    else{
                        UserRelationship::where('id',$checked->id)->update([
                            'status' => UserRelationship::DELETE_STATUS,
                            'updated_at' => date("Y-m-d H:i:s")
                        ]);
                        return $this->successResponse($this->getMessageNoti('NOTI_118'));

                    }
                    
                }
                $relation = UserRelationship::where('id',$checked->id)->first();
            }
            if(!$relation){
                return $this->errorResponse($this->getMessageError("ERROR_181"),400,"ERROR_181");
            }
           
            $checkNoti = ModelsNotificationRelationship::where('relationship_id',$relation->id)->first();
            if(!$checkNoti){
                ModelsNotificationRelationship::insert([
                    'status' => ($relation->status == UserRelationship::REQUEST_STATUS) ? ModelsNotificationRelationship::REQUEST_STATUS : ModelsNotificationRelationship::FRIEND_STATUS,
                    'relationship_id' => $relation->id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }
            else{
                ModelsNotificationRelationship::where('id',$checkNoti->id)->update([
                    'status' => ($relation->status == UserRelationship::REQUEST_STATUS) ? ModelsNotificationRelationship::REQUEST_STATUS : ModelsNotificationRelationship::FRIEND_STATUS,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
                
            }
            
            broadcast(new NotificationRelationship($userTwo,$relation,UserRelationship::MESSAGE_REQUEST));
            broadcast(new NotificationRelationship($userOne,$relation,UserRelationship::MESSAGE_REQUEST));
            if($relation->status == UserRelationship::FRIEND_STATUS){
                return $this->successResponse(['message'=>$this->getMessageNoti('NOTI_119'),'relation'=>$relation]);

            }
            return $this->successResponse(['message'=>$this->getMessageNoti('NOTI_117'),'relation'=>$relation]);
           
        }
        else {
            if(!$checked){
                return $this->errorResponse($this->getMessageError("ERROR_187"),400,"ERROR_187");
            }
            if($checked->status == UserRelationship::DELETE_STATUS){
                return $this->errorResponse($this->getMessageError("ERROR_187"),400,"ERROR_187");
            }
            else{
                broadcast(new NotificationRelationship($userTwo,$checked,UserRelationship::MESSAGE_DELETE));
                broadcast(new NotificationRelationship($userOne,$checked,UserRelationship::MESSAGE_DELETE));
                $noti = ModelsNotificationRelationship::where('relationship_id',$checked->id)->first();
                ModelsNotificationRelationship::where('id',$noti->id)->update([
                    'status' => ModelsNotificationRelationship::DELETE_STATUS,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
                UserRelationship::where('id',$checked->id)->update([
                    'status' => 'deleted',
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }
            return $this->successResponse($this->getMessageNoti('NOTI_118'));
        }
        
    }

    public function acceptRequest(Request $request){
        $validator = Validator::make($request->all(),[
            'accepted' => 'boolean|required',
            'user_two' => 'int|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $userOne = Auth::user();
        $userTwo = User::where('id',$request->user_two)->first();
        if(!$userTwo){
            return $this->errorResponse($this->getMessageError("ERROR_108"),400,"ERROR_108");
        }
        if($userOne->id == $userTwo->id){
            return $this->errorResponse($this->getMessageError("ERROR_186"),400,"ERROR_186");
        }
        $checked = UserRelationship::where('user_one',$userTwo->id)
            ->where('user_two',$userOne->id)
            ->first();
        if(!$checked){
            return $this->errorResponse($this->getMessageError("ERROR_189"),400,"ERROR_189");
        }
        if($checked->status == "friend"){
            return $this->errorResponse($this->getMessageError("ERROR_188"),400,"ERROR_188");
        }
        if($request->accepted == 1){
            UserRelationship::where('id',$checked->id)->update([
                'status' => 'friend'
            ]);
            $relation =  UserRelationship::where('id',$checked->id)->first();
            $checkNoti = ModelsNotificationRelationship::where('relationship_id',$relation->id)->first();
            if(!$checkNoti){
                ModelsNotificationRelationship::insert([
                    'status' => ModelsNotificationRelationship::FRIEND_STATUS,
                    'relationship_id' => $relation->id,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }
            else{
                ModelsNotificationRelationship::where('id',$checkNoti->id)->update([
                    'status' => ModelsNotificationRelationship::FRIEND_STATUS,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
                }
                broadcast(new NotificationRelationship($userTwo,$relation,UserRelationship::MESSAGE_REQUEST));
                broadcast(new NotificationRelationship($userOne,$relation,UserRelationship::MESSAGE_REQUEST));
            return $this->successResponse(['message'=>$this->getMessageNoti('NOTI_119'),'relation'=>UserRelationship::where('id',$checked->id)->first()]);
        }
        else{
            if(!$checked){
                return $this->errorResponse($this->getMessageError("ERROR_187"),400,"ERROR_187");
            }
            if($checked->status == UserRelationship::FRIEND_STATUS){
                return $this->errorResponse($this->getMessageError("ERROR_185"),400,"ERROR_185");
            }
            else{
                $noti = ModelsNotificationRelationship::where('relationship_id',$checked->id)->first();
                ModelsNotificationRelationship::where('id',$noti->id)->update([
                    'status' => ModelsNotificationRelationship::DELETE_STATUS,
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
                UserRelationship::where('id',$checked->id)->update([
                    'status' => 'deleted',
                    'updated_at' => date("Y-m-d H:i:s")
                ]);
            }
            return $this->successResponse($this->getMessageNoti('NOTI_118'));
        }
        
        return $this->successResponse($this->getMessageNoti('NOTI_120'));
    }

    public function getNoti(){
        $user = Auth::user();
        $relation = json_decode(UserRelationship::where('user_relationships.user_one',$user->id)
                ->orWhere('user_relationships.user_two',$user->id)
                ->get());
        if(!$relation){
            $data = array();
            return $this->successResponse($data);
        }
        else{
            $data = array();
            for ($i=0; $i < count($relation); $i++) { 
                $noti = ModelsNotificationRelationship::with('relationship.userOne','relationship.userTwo')
                    ->where('relationship_id',$relation[$i]->id)
                    ->where('status','<>',ModelsNotificationRelationship::DELETE_STATUS)
                    ->first();
                if($noti){
                    array_push($data,$noti);
                }
            }
        }
        
        return $this->successResponse($data);
    }

    public function showRequestFriend(){
        $currentUser = Auth::user();
        $relaUser = UserRelationship::where('user_two',$currentUser->id)
                ->where('status','requested')
                ->get();
        $arrId = array();
        for ($i=0; $i < count($relaUser); $i++) { 
            if($relaUser[$i]->user_one != $currentUser->id ){
                array_push($arrId,$relaUser[$i]->user_one);
            }
        }
        $listUser = User::whereIn('id',$arrId)->paginate(10);
        return $this->successResponse($listUser);
    }

    public function showFriends(){
        $currentUser = Auth::user();
        $relaUser = UserRelationship::where(function($query) use($currentUser){
                        $query->where('user_one',$currentUser->id)
                            ->orWhere('user_two',$currentUser->id);
                    })
                    ->where('status','friend')
                    ->get();
        // if(count($relaUser) ==0){
        //     return $this->errorResponse($this->getMessageError("ERROR_190"),400,"ERROR_190");
        // }
        $arrId = array();
        for ($i=0; $i < count($relaUser); $i++) { 
            if($relaUser[$i]->user_one != $currentUser->id ){
                array_push($arrId,$relaUser[$i]->user_one);
            }
            else if($relaUser[$i]->user_two != $currentUser->id){
                array_push($arrId,$relaUser[$i]->user_two);
            }
        }
        $listUser = User::whereIn('id',$arrId)->paginate(10);
        return $this->successResponse($listUser);
    }

    public function findFriend(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'string|required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }
        $currentUser = Auth::user();
        $relaUser = UserRelationship::where(function($query) use($currentUser){
                        $query->where('user_one',$currentUser->id)
                            ->orWhere('user_two',$currentUser->id);
                    })
                    ->where('status','friend')
                    ->get();
        if(count($relaUser) ==0){
            return $this->errorResponse($this->getMessageError("ERROR_190"),400,"ERROR_190");
        }
        $arrId = array();
        for ($i=0; $i < count($relaUser); $i++) { 
            if($relaUser[$i]->user_one != $currentUser->id ){
                array_push($arrId,$relaUser[$i]->user_one);
            }
            else if($relaUser[$i]->user_two != $currentUser->id){
                array_push($arrId,$relaUser[$i]->user_two);
            }
        }
        $friend = User::whereIn('id',$arrId)->where('name','like','%'.$request->name.'%')->get();

        return $this->successResponse($friend);
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
