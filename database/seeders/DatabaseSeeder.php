<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::create([
            'name' => 'AdminUser',
            'email' => 'admin@admin.com',
            'password' => \bcrypt('U$erAdm1n'),
            'email_verified_at' => Carbon::now()->timestamp,
            'role' => 'admin'
        ]);

        $this->call([
            AuthorsTableSeeder::class,
            BooksTableSeeder::class,
            LoansTableSeeder::class,
        ]);
    }
}
