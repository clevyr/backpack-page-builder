<?php

namespace Clevyr\PageBuilder\app\Console\Commands;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Class CreateUser
 * @package Clevyr\PageBuilder\app\Console\Commands
 */
class CreateUser extends Command
{
    use PrettyCommandOutput;

    /**
     * @var $progressBar
     */
    protected $progressBar;

    /**
     * @var string $signature
     */
    protected $signature = 'pagebuilder:user
                                {--N|name= : The name of t he new user}
                                {--E|email= : The user\'s email address}
                                {--P|password= : User\'s password}
                                {--encrypt=true : Encrypt user\'s password if it\'s plain text (True by default)}';

    /**
     * @var string $description
     */
    protected $description = 'Create a new super admin';

    /**
     * Handle
     *
     * @return void
     */
    public function handle() : void
    {
        $this->info('Creating a new user');

        // Name
        if (!$name = $this->option('name')) {
            $name = $this->ask('Name');
        }

        // Email
        if (!$email = $this->option('email')) {
            $email = $this->ask('Email');
        }

        // Password
        if (!$this->option('password')) {
            $password = $this->ask('Password');
        } else {
            $password = $this->option('password');
        }

        // Encrypt
        if ($this->option('encrypt')) {
            $password = Hash::make($password);
        }

        $auth = config('backpack.base.user_model_fnq', 'App\User');

        $user = new $auth();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->assignRole('Super Admin');

        if ($user->save()) {
            $this->info('Successfully created new user');
        } else {
            $this->error('Something went wrong trying to save your user');
        }
    }
}
