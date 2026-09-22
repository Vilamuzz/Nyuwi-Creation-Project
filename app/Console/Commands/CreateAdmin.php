<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateAdmin extends Command
{
    protected $signature = 'create:admin
        {--name= : Name of the admin user}
        {--email= : Email address of the admin user}
        {--password= : Password for the admin user}';

    protected $description = 'Create a new admin user (requires server/CLI access)';

    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        $name = $name !== null ? $name : text('Admin name', required: true);
        $email = $email !== null ? $email : text('Admin email', required: true);
        $password = $password !== null ? $password : password('Admin password', required: true);

        $email = strtolower(trim($email));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
        ]);

        $this->info("Admin user '{$email}' created successfully.");

        return self::SUCCESS;
    }
}