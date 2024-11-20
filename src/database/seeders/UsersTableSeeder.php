<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'スタッフ１',
            'email' => 'staffuser@email.com',
            'password' => Hash::make('staffuser1'),
        ];
        DB::table('users')->insert($param);
    }
}
