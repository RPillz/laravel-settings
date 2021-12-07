<?php

namespace RPillz\Settings\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use RPillz\Settings\SettingsServiceProvider;

class TestCase extends Orchestra
{
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Factory::guessFactoryNamesUsing(
        //     fn (string $modelName) => 'RPillz\\Settings\\Database\\Factories\\'.class_basename($modelName).'Factory'
        // );

        $this->setUpDatabase($this->app);

        $this->testUser = User::where('email', 'test@example.com')->first();
    }

    protected function getPackageProviders($app)
    {
        return [
            SettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__.'/../database/migrations/create_settings_table.php.stub';
        $migration->up();
    }

    protected function setUpDatabase($app)
    {
        // fake users table
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        User::create(['email' => 'test@example.com']);
    }
}
