<?php

namespace App\Livewire;

use Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Map")]
class ShowMap extends Component
{
    public function render()
    {
        $user = Auth::user();
        $role = $user ? $user->role : 'guest';
        
        return view('livewire.show-map', ['role'=>$role, 'name'=>$user?->name])->layoutData(['role'=>$role]);
    }
}
