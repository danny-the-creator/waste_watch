<?php
use App\Livewire\Login;
use App\Livewire\ManageAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

test('guests can view the map and login pages', function () {
    $this->get('/map')->assertStatus(200);
    $this->get('/login')->assertStatus(200);
});

test('unauthenticated user cannot access restricted pages', function () {
    $this->get('/account')->assertRedirect('/login');
});

test('employee cannot access admin pages', closure: function () {
    $user = User::factory()->create(['role'=>'employee']);
    $this->actingAs($user)->get('/users')->assertRedirect('/login');
    $this->actingAs($user)->get('/messages')->assertRedirect('/login');
});

test('allows employees to log in with correct credentials', function () {
    $user = User::factory()->create(['role'=>'employee']);
    
    Livewire::test(Login::class)
    ->set('email', $user->email)
    ->set('password', 'password')
    ->call('login')
    ->assertRedirect('/map');

    $this->assertAuthenticated();
    $this->assertEquals(Auth::user()->role, 'employee');
});

test('allows admins to log in with correct credentials', function () {
    $user = User::factory()->create(['role'=>'admin']);
    
    Livewire::test(Login::class)
    ->set('email', $user->email)
    ->set('password', 'password')
    ->call('login')
    ->assertRedirect('/map');

    $this->assertAuthenticated();
    $this->assertEquals(Auth::user()->role, 'admin');
});

test ('does not allow logging in with incorrect credentials', function () {
    $user = User::factory()->create();

    // invalid password
    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', '')
        ->call('login')
        ->assertHasErrors('password');

    // invalid credentials
    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong_password')
        ->call('login')
        ->assertHasErrors('email');
});

test('allows users to logout', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ManageAccount::class)
        ->call('logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});