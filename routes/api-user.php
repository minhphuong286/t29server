<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MessageGroupController;
use App\Http\Controllers\MessagePrivateController;
use App\Http\Controllers\RelationshipController;
use App\Http\Controllers\SendNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware(['cors'])->group(function () {
    
// });
Route::prefix('auth')->group(function(){
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
Route::get('/users/{phone}', [MessageGroupController::class, 'getDetailUser'])->middleware('auth');
Route::post('/find-user', [MessageGroupController::class, 'findUser'])->middleware('auth');
Route::prefix('group-chat')->group(function(){
    Route::post('/create', [MessageGroupController::class, 'create'])->middleware('auth');
    Route::post('/{presence_room_id}/chat', [MessageGroupController::class, 'store'])->middleware('auth');
    Route::post('/{presence_room_id}/seen-message', [MessageGroupController::class, 'seenMessage'])->middleware('auth');
    Route::get('/{presence_room_id}/messages', [MessageGroupController::class, 'showMessage'])->middleware('auth');
    Route::get('/{presence_room_id}/members', [MessageGroupController::class, 'memberGroup'])->middleware('auth');
    Route::get('/{presence_room_id}/num-members', [MessageGroupController::class, 'countMember'])->middleware('auth');
    Route::delete('/{presence_room_id}/out-room', [MessageGroupController::class, 'outRoom'])->middleware('auth');
    Route::post('/{presence_room_id}/video-call', [MessageGroupController::class, 'callGroup'])->middleware('auth');
    Route::post('/{presence_room_id}/authorize-user/{user_id}', [MessageGroupController::class, 'authorizeUser'])->middleware('auth');
    Route::delete('/{presence_room_id}/kick-user/{user_id}', [MessageGroupController::class, 'kickUser'])->middleware('auth');
    Route::post('/video-call-finish', [MessageGroupController::class, 'rejectCall'])->middleware('auth');
    Route::post('/video-call-accept', [MessageGroupController::class, 'callingStatus'])->middleware('auth');
    Route::post('/{vcg_id}/call-acception', [MessageGroupController::class, 'acceptionCall'])->middleware('auth');
    Route::post('/{vcg_id}/call-close', [MessageGroupController::class, 'closeCall'])->middleware('auth');
    Route::post('/add-member', [MessageGroupController::class, 'addMember'])->middleware('auth');
    Route::get('/list-rooms', [MessageGroupController::class, 'showPresenceRooms'])->middleware('auth');
    Route::get('/{id}', [MessageGroupController::class, 'getDetailRoom'])->middleware('auth');
});

Route::prefix('private-chat')->group(function(){
    Route::post('/create', [MessagePrivateController::class, 'createRoom'])->middleware('auth');
    Route::post('/{private_room_id}/chat', [MessagePrivateController::class, 'store'])->middleware('auth');
    Route::post('/{private_room_id}/video-call', [MessagePrivateController::class, 'call'])->middleware('auth');
    Route::post('/{private_room_id}/video-call-accept', [MessagePrivateController::class, 'callAccept'])->middleware('auth');
    Route::post('/{private_room_id}/video-call-close', [MessagePrivateController::class, 'closeCall'])->middleware('auth');
    Route::post('/{private_room_id}/video-call-reject', [MessagePrivateController::class, 'rejectCall'])->middleware('auth');
    Route::get('/{private_room_id}/messages', [MessagePrivateController::class, 'showMessage'])->middleware('auth');
    Route::post('/{private_room_id}/notification', [SendNotification::class, 'sendPrivate'])->middleware('auth');
    Route::post('/{private_room_id}/seen-message', [MessagePrivateController::class, 'seenMessage'])->middleware('auth');
    Route::get('/list-rooms', [MessagePrivateController::class, 'showPrivateRooms'])->middleware('auth');
});

Route::prefix('relationship')->group(function(){
    Route::post('/friend-request', [RelationshipController::class, 'sendRequest'])->middleware('auth');
    Route::post('/friend-accept', [RelationshipController::class, 'acceptRequest'])->middleware('auth');
    Route::get('/list-friends', [RelationshipController::class, 'showFriends'])->middleware('auth');
    Route::get('/notifications', [RelationshipController::class, 'getNoti'])->middleware('auth');
    Route::get('/list-request-friends', [RelationshipController::class, 'showRequestFriend'])->middleware('auth');
    Route::post('/find-friends', [RelationshipController::class, 'findFriend'])->middleware('auth');
});




    