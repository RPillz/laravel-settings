<?php

namespace RPillz\Settings\Commands;

use Illuminate\Console\Command;
use RPillz\Settings\Facades\Settings;

class SetCommand extends Command
{
    public $signature = 'settings:set {key} {value}';

    public $description = 'Set a key-value pair in the database settings';

    public function handle(): int
    {
        $key = $this->argument('key');

        $value = $this->argument('value');

        Settings::set($key, $value);

        $this->comment($key . ' has been set to: ' . $value);

        return self::SUCCESS;
    }
}
