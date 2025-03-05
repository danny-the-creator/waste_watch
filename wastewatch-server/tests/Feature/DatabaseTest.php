<?php
use App\Models\Log;
use App\Models\Trashcan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('required Schemas exist', function () {
    $this->assertTrue(Schema::hasTable('users'));
    $this->assertTrue(Schema::hasTable('trashcans'));
    $this->assertTrue(Schema::hasTable('logs'));
});

test('users schema has the right columns', function () {
    $this->assertTrue(Schema::hasColumn('users', 'name'));
    $this->assertTrue(Schema::hasColumn('users', 'email'));
    $this->assertTrue(Schema::hasColumn('users', 'password'));
    $this->assertTrue(Schema::hasColumn('users', 'role'));
});

test('trashcans schema has the right columns', function() {
    $this->assertTrue(Schema::hasColumn('trashcans', 'location'));
    $this->assertTrue(Schema::hasColumn('trashcans', 'fill_level'));
    $this->assertTrue(Schema::hasColumn('trashcans', 'lid_blocked'));
    $this->assertTrue(Schema::hasColumn('trashcans', 'servicing_lid_blocked'));
});

test('logs schema has the right columns', function() {
    $this->assertTrue(Schema::hasColumn('logs', 'user_id'));
    $this->assertTrue(Schema::hasColumn('logs', 'trashcan_id'));
    $this->assertTrue(Schema::hasColumn('logs', 'action'));
    $this->assertTrue(Schema::hasColumn('logs', 'timestamp'));
    $this->assertTrue(Schema::hasColumn('logs', 'result'));
});

//INTEGRITY TESTS
test('nullOnDelete on foreign keys are enforced', function() {
    // TODO: Foreign key constraints testing
    $user = User::factory()->create();
    $trashcan = Trashcan::factory()->create();
    $log = Log::factory()->create(['user_id'=>$user->id, 'trashcan_id' => $trashcan->id]);
    
    $user->delete();
    $log->refresh();
    $this->assertNull($log->user_id);
    
    $trashcan->delete();
    $log->refresh();
    $this->assertNull($log->trashcan_id);    
});

test('foreign key constraints are enforced', function() {
    $user = User::factory()->create();
    $uid = $user->id;
    $user->delete();
    $user->refresh();

    $this->expectException(QueryException::class);
    Log::factory()->create(['user_id'=>$uid]);
});

test('unique constraint on email is enforced', function() {
    User::factory()->create(['email'=>'bilbo.baggins@bagend.shire']);

    $this->expectException(QueryException::class);
    User::factory()->create(['email'=>'bilbo.baggins@bagend.shire']);
});