<div class="p-6 flex flex-col" style="height: 75vh;" wire:poll.5s>
    {{-- Kotak Pesan --}}
    <div 
        x-data="{
            scrollToBottom() {
                this.$el.scrollTop = this.$el.scrollHeight;
            }
        }"
        x-init="scrollToBottom()"
        @message-sent.window="setTimeout(() => scrollToBottom(), 100)"
        id="chat-box" 
        class="flex-grow space-y-4 overflow-y-auto p-4 border border-slate-700 rounded-md mb-4 bg-slate-900"
    >
        @forelse ($messages as $msg)
            <div class="flex items-end space-x-2 @if($msg->sender_id == Auth::id()) flex-row-reverse space-x-reverse @endif">
                
           

                <div>
                 @if ($msg->sender->role === 'psikolog' && $msg->sender->psychologistProfile?->profile_image_path)
                    
                     <img src="{{ asset('storage/' . $msg->sender->psychologistProfile->profile_image_path) }}" alt="{{ $msg->sender->name }}" class="w-8 h-8 rounded-full object-cover">
                @else
                
                <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-white font-bold text-sm">
                {{ strtoupper(substr($msg->sender->name, 0, 1)) }}
                </div>
                 @endif
                </div>

                {{-- Gelembung Chat --}}
                <div class="max-w-xs md:max-w-lg rounded-lg p-3 @if($msg->sender_id == Auth::id()) bg-cyan-600 text-white @else bg-slate-700 text-slate-200 @endif">
                  
                    @if($msg->sender_id != Auth::id())
                    <p class="text-xs font-bold text-cyan-300 mb-1">{{ $msg->sender->name }}</p>
                    @endif
                    <p class="text-sm" style="white-space: pre-wrap;">{{ $msg->message }}</p>
                    <p class="text-xs opacity-75 mt-1 text-right">{{ $msg->created_at->format('H:i') }}</p>
                </div>

            </div>
        @empty
             <div class="text-center text-slate-500 h-full flex items-center justify-center">
                Mulai percakapan Anda dengan {{ $contact->name }}.
            </div>
        @endforelse
    </div>


<form 
    wire:submit.prevent="sendMessage" 
    @message-sent.window="document.getElementById('message-input').value = ''"
>
    <div class="flex space-x-2">
        <x-text-input 
            wire:model="message" 
            id="message-input" 
            class="flex-grow" 
            placeholder="Ketik pesan..." 
            required 
            autocomplete="off" 
        />
        <x-primary-button type="submit">Kirim</x-primary-button>
    </div>
    @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</form>
</div>
