<?php
  
namespace Database\Seeders;
  
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
 
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [];
        for($i = 0; $i < 4; $i++) {
            if($i == 0) {
                $data = [
                    'name' => 'Admin'
                ];
            }
            elseif($i == 1) {
                $data = [
                    'name' => 'Telemarketing'
                ];    
            }
            elseif($i == 2) {
                $data = [
                    'name' => 'Chakra'
                ];
            }
            elseif($i == 3) {
                $data = [
                    'name' => 'Order Handling'
                ]; 
            }
            Role::create($data);
        }
    }
}