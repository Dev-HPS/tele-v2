<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
  
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Ardy Surya',
            'email' => 'admin@gmail.com',
            'username' => 'sulivanz',
            'password' => Hash::make('asdjkl123'),
            'sbu_code' => 'HPS',
            'role_id' => '346d417a-544d-48f3-bb4d-1da4ce54dffc'
        ]);
    }
}