<?php

namespace App\Livewire;

use Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Login')]
class Login extends Component
{
    #[Validate('required|email')]
    public $email= '';

    #[Validate('required|min:8')]
    public $password = '';

    public $remember_password = false;

    public function close_error() {
        $this->resetErrorBag('authorization');
    }

    public function login() 
    {
        $this->resetErrorBag('authorization');
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember_password)) {
            session()->flash('message', 'You have successfully logged in!');
            return $this->redirectRoute('map', navigate: true);
        }

        //auth failed
        $this->addError('email', 'The provided email and/or password is not correct');
    }

    public function forgot_password()
    {
        return;
    }

    public function render()
    {
        if (Auth::check()) $this->redirect('map', navigate: true);
        return view('livewire.login')->layoutData(['role'=>'guest']);
    }
}
