<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupApiDocumentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:setup-docs {--force : Force setup even if already configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup API documentation with L5 Swagger';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up API Documentation...');
        $this->line('');

        // Check if L5 Swagger is installed
        if (!$this->checkL5SwaggerInstalled()) {
            $this->error('❌ L5 Swagger is not installed. Please install it first:');
            $this->line('   composer require darkaonline/l5-swagger');
            return 1;
        }

        // Publish L5 Swagger config if needed
        if (!File::exists(config_path('l5-swagger.php')) || $this->option('force')) {
            $this->info('📝 Publishing L5 Swagger configuration...');
            $this->call('vendor:publish', [
                '--provider' => 'L5Swagger\L5SwaggerServiceProvider'
            ]);
        }

        // Create storage directories
        $this->createStorageDirectories();

        // Generate initial documentation
        $this->info('📖 Generating API documentation...');
        $this->call('l5-swagger:generate');

        // Display setup summary
        $this->displaySetupSummary();

        $this->line('');
        $this->info('✅ API Documentation setup completed successfully!');

        return 0;
    }

    /**
     * Check if L5 Swagger is installed
     */
    private function checkL5SwaggerInstalled(): bool
    {
        return class_exists('L5Swagger\L5SwaggerServiceProvider');
    }

    /**
     * Create necessary storage directories
     */
    private function createStorageDirectories(): void
    {
        $directories = [
            storage_path('api-docs'),
            public_path('docs'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->line("   Created directory: {$directory}");
            }
        }
    }

    /**
     * Display setup summary
     */
    private function displaySetupSummary(): void
    {
        $this->line('');
        $this->line('📋 <comment>Setup Summary:</comment>');
        $this->line('');
        
        $this->line('🔗 <comment>API Endpoints:</comment>');
        $this->line('   • Documentation UI: ' . config('app.url') . '/api/documentation');
        $this->line('   • JSON Specification: ' . config('app.url') . '/docs/api-docs.json');
        $this->line('   • Health Check: ' . config('app.url') . '/api/health');
        $this->line('');

        $this->line('📚 <comment>Available API Groups:</comment>');
        $apiGroups = [
            'Authentication' => '5 endpoints (login, register, logout, etc.)',
            'Assessment Sessions' => '7 endpoints (CRUD + clone, finalize)',
            'Assessment Results' => '4 endpoints (update, AI insights)',
            'Community Templates' => '7 endpoints (share, use, rate)',
            'Intelligence & Analytics' => '6 endpoints (dashboard, reports)',
            'Compliance Workspace' => '4 endpoints (SoA management)',
            'Knowledge Base' => '6 endpoints (documentation)',
            'User Profile' => '3 endpoints (profile management)',
            'Webhooks' => '4 endpoints (N8N integration)',
        ];

        foreach ($apiGroups as $group => $description) {
            $this->line("   • {$group}: {$description}");
        }

        $this->line('');
        $this->line('🔧 <comment>Next Steps:</comment>');
        $this->line('   1. Visit /api/documentation to explore the API');
        $this->line('   2. Test endpoints using the interactive Swagger UI');
        $this->line('   3. Generate API tokens for authentication');
        $this->line('   4. Integrate with your frontend application');
        $this->line('');
        
        $this->line('💡 <comment>Tips:</comment>');
        $this->line('   • Use /api/health for monitoring');
        $this->line('   • Check rate limits in response headers');
        $this->line('   • All responses follow consistent JSON format');
        $this->line('   • File uploads use multipart/form-data');
    }
}