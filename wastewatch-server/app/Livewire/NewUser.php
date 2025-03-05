<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Str;

class NewUser extends Component
{
    public bool $open = false;
    
    #[Validate('required')]
    public ?string $name = '';
    #[Validate('required|email|unique:users,email')]
    public ?string $email = '';

    public ?string $password = '';

    #[Validate('required')]
    public ?string $role = '';

    #[On("new-user")]
    public function openModal() {
        $this->open = true;
        $this->password = Str::random(8);
    }

    public function save() {
        $this->validate();
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ]);

        $this->open = false;
        $this->reset('name', 'email', 'password', 'role');
        $this->dispatch('refresh-table');
    }

    public function close() {
        $this->open = false;
    }

    


    public function render()
    {
        return view('livewire.new-user');
    }
}
