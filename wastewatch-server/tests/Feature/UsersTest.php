<?php
use App\Livewire\UserControl;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

test('page loads correct component when authorized', function () {
    $user = User::factory()->create(['role'=>'admin']);
    $this->actingAs($user)->get('/devices')->assertSeeLivewire(UserControl::class);
});

test('all users appear in the list', function () {
    User::factory(2)->create();
    Livewire::test(UserControl::class)
        ->assertViewHas('users', function($users) {
                return count($users) == User::count();
            });
});

test('admin can add users', function () {
    $this->assertTrue(false);
});
test('admin can edit users', function () {
    $this->assertTrue(false);
});
