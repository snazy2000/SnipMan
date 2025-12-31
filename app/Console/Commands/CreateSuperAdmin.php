<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin
                            {--email= : The email address of the super admin}
                            {--password= : The password for the super admin}
                            {--name= : The name of the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating Super Admin User...');
        $this->newLine();

        // Get or prompt for name
        $name = $this->option('name') ?: $this->ask('Name', 'Super Admin');

        // Get or prompt for email
        $email = $this->option('email') ?: $this->ask('Email address');

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('email'));

            return 1;
        }

        // Get or prompt for password
        $password = $this->option('password') ?: $this->secret('Password (min 8 characters)');

        // Validate password
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');

            return 1;
        }

        // Confirm password if not provided via option
        if (! $this->option('password')) {
            $passwordConfirmation = $this->secret('Confirm Password');

            if ($password !== $passwordConfirmation) {
                $this->error('Passwords do not match.');

                return 1;
            }
        }

        // Create the super admin user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_super_admin' => true,
            ]);

            $this->newLine();
            $this->info('✓ Super admin user created successfully!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Super Admin', 'Yes'],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create super admin user: '.$e->getMessage());

            return 1;
        }
    }
}
