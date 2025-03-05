<?php

use App\Http\Controllers\ApiHandler;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Employee;
use App\Livewire\Login;
use App\Livewire\ManageAccount;
use App\Livewire\ShowMap;
use App\Livewire\ShowMessages;
use App\Livewire\TrashcanControl;
use App\Livewire\UserControl;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login');
Route::get('login', Login::class)->name('login');
Route::get('map', ShowMap::class)->name('map');

Route::middleware(Employee::class)->group(function () {
	Route::get('devices', TrashcanControl::class)->name('devices');	
	Route::get('account', ManageAccount::class)->name('account');
});

Route::middleware(Admin::class)->group(function(){
	Route::get('users', UserControl::class)->name('users');
	Route::get('messages', ShowMessages::class)->name('messages');
});