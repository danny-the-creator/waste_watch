<?php

namespace App\Livewire;

use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title("Manage Users")]
class UserControl extends Component
{
    public int $entriesOnPage = 10;
    public string $search_query = '';

    #[Computed()]
    public function users(): Collection {
        return User::whereAny(['name', 'role', 'id'], 'LIKE', '%'.$this->search_query.'%')->limit($this->entriesOnPage)->get();
    }

    #[On("refresh-table")]
    public function reload() {
        return $this->render();
    }

    public function loadMoreEntries() {
        $this->entriesOnPage += 5;
    }

    public function new()
    {   
        $this->dispatch("new-user");
    }

    
    public function render()
    {
        $user = Auth::user();
        $role = $user ? $user->role : 'guest';
        
        return view('livewire.user-control', ['authuser'=>$user])->layoutData(['role'=>$role]);
    }
}
