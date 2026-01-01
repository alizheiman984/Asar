<?php

namespace App\Http\Controllers\Api;

use App\Models\Chat;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::with(['sender', 'receiver'])->paginate(10);
        return ChatResource::collection($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'sender_type' => 'required|in:volunteer,team',
            'receiver_type' => 'required|in:volunteer,team',
        ]);

        $chat = Chat::create($request->all());
        return new ChatResource($chat);
    }

    public function show(Chat $chat)
    {
        return new ChatResource($chat->load(['sender', 'receiver']));
    }

    public function update(Request $request, Chat $chat)
    {
        $request->validate([
            'message' => 'sometimes|string',
            'sender_id' => 'sometimes|exists:users,id',
            'receiver_id' => 'sometimes|exists:users,id',
            'sender_type' => 'sometimes|in:volunteer,team',
            'receiver_type' => 'sometimes|in:volunteer,team',
        ]);

        $chat->update($request->all());
        return new ChatResource($chat);
    }

    public function destroy(Chat $chat)
    {
        $chat->delete();
        return response()->json(['message' => 'Chat message deleted successfully']);
    }

    public function getChatHistory($senderId, $receiverId)
    {
        $chats = Chat::where(function($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return ChatResource::collection($chats);
    }


  public function myChatRooms()
    {
        $user = auth()->user();
        $userType = get_class($user);

        $chatRooms = ChatRoom::where(function ($q) use ($user, $userType) {

                $q->whereHas('messages', function ($query) use ($user, $userType) {
                    $query->where('sender_id', $user->id)
                        ->where('sender_type', $userType);
                });

                if ($user instanceof \App\Models\Volunteer) {
                    $q->orWhereHas('volunteers', function ($query) use ($user) {
                        $query->where('volunteers.id', $user->id);
                    });
                }

                if ($user instanceof \App\Models\Employee) {
                    $q->orWhere('employee_id', $user->id);
                }
            })
            ->with(['campaign', 'messages.sender'])
            ->latest()
            ->get();

        if ($chatRooms->isEmpty()) {
            return response()->json([
                'message' => 'لا يوجد محادثات',
                'data' => []
            ]);
        }

        return response()->json([
            'message' => 'تم جلب المحادثات',
            'data' => $chatRooms
        ]);
    }



    public function sendMessage(Request $request, $chatRoomId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chatRoom = ChatRoom::find($chatRoomId);

        if(!$chatRoom){
            return response()->json(['message' => 'ChatRoom not found'], 404);
        }
        $message = $chatRoom->messages()->create([
            'sender_id' => auth()->id(),
            'sender_type' => get_class(auth()->user()),
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load('sender')
        ]);
    }


    public function getMessages($chatRoomId)
    {
        $chatRoom = ChatRoom::find($chatRoomId);

        if(!$chatRoom){
            return response()->json([
                'message' => 'ChatRoom not found',
                'data' => []
            ]);
        }

        $messages = $chatRoom->messages()->with('sender')->latest()->get();

        return response()->json($messages);
    }


} 