<?php
use App\Livewire\TrashcanControl;
use App\Models\Trashcan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

test('page loads correct component when authorized', function () {
    $user = User::factory()->create(['role'=>'admin']);
    $this->actingAs($user)->get('/devices')->assertSeeLivewire(TrashcanControl::class);
});

test('all devices appear in the list', function () {
    Trashcan::factory(2)->create();
    Livewire::test(TrashcanControl::class)
        ->assertViewHas('trashcans', function($trashcans) {
                return count($trashcans) == Trashcan::count();
            });
});

test('employee cannot add or edit devices', function () {
    $user = User::factory()->create(['role'=>'employee']);
    Livewire::actingAs($user)
        ->test(TrashcanControl::class)
        ->call('new')
        ->assertHasErrors('unauthorized');
    Livewire::actingAs($user)
        ->test(TrashcanControl::class)
        ->call('edit')
        ->assertHasErrors('unauthorized');
});

test('employee can view more information about devices', function () {
    $user = User::factory()->create(['role'=>'employee']);
    $trashcan = Trashcan::factory()->create();
    Livewire::actingAs($user)
        ->test(TrashcanControl::class)
        ->call('view', $trashcan->id)
        ->assertHasNoErrors('unauthorized')
        ->assertSee($trashcan->description);
});

test('admin can add devices', function () {
    $this->assertTrue(false);
});
test('admin can edit devices', function () {
    $this->assertTrue(false);
});
