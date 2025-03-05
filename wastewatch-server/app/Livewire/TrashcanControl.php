<?php

namespace App\Livewire;

use App\Http\Controllers\ApiClient;
use App\Models\Trashcan;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;


#[Title("Manage Trashcans")]
class TrashcanControl extends Component
{  
    public int $entriesOnPage = 10;
    public string $search_query = '';

    #[Computed()]
    public function trashcans(): Collection {
        return Trashcan::whereAny(['tag', 'id'], 'LIKE', '%'.$this->search_query.'%')->limit($this->entriesOnPage)->get();
    }

    #[On("refresh-table")]
    public function reload() {
        return $this->render();
    }

    public function loadMoreEntries() {
        $this->entriesOnPage += 5;
    }


    public function edit(int $id)
    {
        $this->dispatch("edit-trashcan", $id);
    }

    public function new()
    {   
        $this->dispatch("new-trashcan");
    }

    public function render()
    {
        $user = Auth::user();
        $role = $user ? $user->role : 'guest';
        
        return view('livewire.trashcan-control')->layoutData(['role'=>$role]);
    }
}
