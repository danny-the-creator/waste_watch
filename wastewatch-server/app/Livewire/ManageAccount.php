<?php

namespace App\Livewire;

use Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Account')]
class ManageAccount extends Component
{

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user ? $user->role : 'guest';
        
        return view('livewire.manage-account')->layoutData(['role'=>$role]);
    }
}
