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

        $chatRooms = ChatRoom::whereHas('messages', function ($query) use ($user, $userType) {
            $query->where('sender_id', $user->id)
                ->where('sender_type', $userType);
        })
        ->orWhereHas('volunteers', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orWhereHas('employees', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['campaign','messages.sender'])
        ->latest()
        ->get();

        return response()->json($chatRooms);
    }


    public function sendMessage(Request $request, $chatRoomId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $chatRoom = ChatRoom::findOrFail($chatRoomId);

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
        $chatRoom = ChatRoom::findOrFail($chatRoomId);

        $messages = $chatRoom->messages()->with('sender')->latest()->get();

        return response()->json($messages);
    }


} 