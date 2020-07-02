<?php

namespace Clevyr\PageBuilder\app\Console\Commands;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

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

    public function handle() : void
    {
        $this->info('Creating a new user');

        if (!$name = $this->option('name')) {
            $name = $this->ask('Name');
        }

        if (!$email = $this->option('email')) {
            $email = $this->ask('Email');
        }

        if (!$this->option('password')) {
            $password = $this->ask('Password');
        }

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
