<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        # permissions
        Permission::create(['name' => 'create-post']);
        # roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'member'])
            ->givePermissionTo('create-post');
        # users
        User::create(['name' => 'Tuấn Anh', 'email' => 'admin@gmail.com', 'password' => bcrypt('Tuananh1997')])
            ->assignRole('admin');
        User::create(['name' => 'Chicken', 'email' => 'chickenbox@gmail.com', 'password' => bcrypt('asdfasdf')])
            ->assignRole('member');
    }
}
