<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:service')]
class MakeServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:service'; // The command name you will type in the terminal

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // This will look for a 'service.stub' file in the 'stubs' directory
        // within your project's root (e.g., /stubs/service.stub).
        // If you prefer to place it elsewhere, adjust this path.
        return base_path('stubs/service.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        // This sets the default location for your service files
        // to App\Services. So, 'make:service UserService' will create
        // App\Services\UserService.php
        return $rootNamespace . '\Services';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        // Defines that the command expects a 'name' argument
        // (e.g., 'make:service UserService')
        return [
            ['name', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The name of the service.'],
        ];
    }
}
