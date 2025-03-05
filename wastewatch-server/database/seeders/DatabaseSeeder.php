<?php

namespace Database\Seeders;

use App\Models\Log;
use App\Models\Trashcan;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            "Bilbo Baggins" => "bilbo.baggins@bagend.me",
            "Frodo Baggins" => "frodo.baggins@bagend.me",
            "Gandalf the Grey" => "mithrandir@istari.valinor",
            "Aragorn" => "strider@rangers.gondor",
            "Gimli" => "axemaster@mines.moria",
            "Samwise Gamgee" => "loyal.gardener@shire.hobbiton",
            "Galadriel" => "lady.of.light@lothlorien.elves",
            "Elrond" => "master.healer@rivendell.haven",
            "Boromir" => "captain@white.tower",
            "Merry Brandybuck" => "meriadoc@brandyhall.buckland",
            "Pippin Took" => "peregrintook@great.smials"
        ];

        
        User::factory()->create([
            'name' => 'Legolas Greenleaf',
            'email' => 'legolas@mirkwood.com',
            'role' => 'admin',
        ]);

        foreach ($users as $name => $email) {
            User::factory()->create([
                'name' => $name,
                'email' => $email,
                'role'=>'employee',
            ]);
        }



        Trashcan::factory(count: 20)->create();

        $log1 = Log::factory()->noUser()->create(['action'=>'full', 'result'=>'danger']);
        $log2 = Log::factory()->create(['action'=>'unlock', 'result'=>'safe', 'trashcan_id'=>$log1->trashcan->id]);
        Log::factory()->create(['action'=>'lock', 'result'=>'safe', 'trashcan_id'=>$log1->trashcan->id, 'user_id'=>$log2->user->id]);

    }
}
