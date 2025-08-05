<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Consultation;
use Illuminate\Support\Facades\Auth;

class ChatRoom extends Component
{
    public Consultation $consultation;
    public $contact;
    public $message = '';

    public function mount(Consultation $consultation)
    {
        $this->consultation = $consultation;
        $this->contact = (Auth::id() === $consultation->user_id) ? $consultation->psychologist : $consultation->user;
    }

    public function render()
    {
        $messages = $this->consultation->chats()
                         ->with('sender.psychologistProfile')
                         ->orderBy('created_at', 'asc')
                         ->get();

        return view('livewire.chat-room', [
            'messages' => $messages
        ]);
    }

    public function sendMessage()
    {
        $this->validate(['message' => 'required|string|max:2000']);

        $receiverId = ($this->consultation->user_id === Auth::id()) ? $this->consultation->psychologist_id : $this->consultation->user_id;

        $this->consultation->chats()->create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'message' => $this->message,
        ]);
        
        // Reset state di backend
        $this->reset('message');

        // Pancarkan event untuk ditangkap oleh JavaScript di frontend
        $this->dispatch('messageSent');
    }
}