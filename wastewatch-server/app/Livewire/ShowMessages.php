<?php

namespace App\Livewire;

use App\Models\Log;
use Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Messages")]
class ShowMessages extends Component
{

    private function generateMessage($action): string {
        return match($action) {
            'unlock' => "Trashcan %s (#%d) has been unlocked",
            'lock' => "Trashcan %s (#%d) has been locked",
            'full' => "Trashcan %s (#%d) is detected full!",
            default => $action
        };
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user ? $user->role : 'guest';

        $messages = Log::all();

        $formatted_messages = [];
        foreach ($messages as $message) {
            $formatted_messages[] = [
                'result' => $message->result,
                'username' => $message->user?->name,
                'timestamp' => strtotime($message->timestamp),
                'message' => $this->generateMessage($message->action),
                'trashcan' => $message->trashcan,
            ];
        }
        
        return view('livewire.show-messages', ['messages' => $formatted_messages])->layoutData(['role'=>$role]);
    }
}
