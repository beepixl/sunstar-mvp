<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

final class GenerateModule extends Command
{
    protected $signature = 'sunstar:make {name : The name of the module (e.g. Credit, Driver, Order)}';
    protected $description = 'Generate model, migration, factory, and Filament resource for a new module.';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        $this->info("🚀 Generating Sunstar module: {$name}");

        Artisan::call("make:model {$name} -m -f");
        $this->info("✅ Model, Migration, and Factory created for {$name}");

        Artisan::call("make:filament-resource {$name}");
        $this->info("✅ Filament Resource created for {$name}");

        $this->newLine();
        $this->info('🎯 Done! You can now edit the generated files:');
        $this->line("  - app/Models/{$name}.php");
        $this->line("  - database/migrations/xxxx_xx_xx_create_" . Str::snake(Str::plural($name)) . "_table.php");
        $this->line("  - database/factories/{$name}Factory.php");
        $this->line("  - app/Filament/Resources/{$name}Resource.php");

        return Command::SUCCESS;
    }
}
