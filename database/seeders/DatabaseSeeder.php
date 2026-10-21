<?php

namespace Database\Seeders;

use App\Models\IdCard;
use App\Models\Invitation;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = Member::factory()->create([
            'email' => 'admin@example.org',
            'username' => 'admin',
        ]);
        $admin->roles()->attach(Role::where('role_name', 'Admin')->value('role_id'));

        // 30 verified members: submitted invitation, completed dues, eligible card.
        Member::factory(30)->create()->each(function (Member $m) {
            Invitation::factory()->submitted()->create(['member_id' => $m->member_id]);
            $dues = Payment::factory()->create(['member_id' => $m->member_id]);
            IdCard::factory()->create([
                'payment_id' => $dues->payment_id,
                'member_id' => $m->member_id,
            ]);
        });

        // 10 invited members who have not submitted the form yet.
        Invitation::factory(10)->create();
    }
}
