<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UtilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		//Default Settings
		DB::table('settings')->insert([
			[
			  'name' => 'mail_type',
			  'value' => 'mail'
			],
			[
			  'name' => 'backend_direction',
			  'value' => 'ltr'
			],		
		]);
		
		
    }
}
