<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-docs {--force : Force regeneration of documentation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation using L5 Swagger';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating API documentation...');

        try {
            // Generate Swagger documentation
            $this->call('l5-swagger:generate');

            $this->info('✅ API documentation generated successfully!');
            $this->line('');
            $this->line('📖 View documentation at: ' . config('app.url') . '/api/documentation');
            $this->line('📄 JSON spec available at: ' . config('app.url') . '/docs/api-docs.json');
            $this->line('');
            
            if ($this->option('force')) {
                $this->warn('Documentation was force regenerated.');
            }

            // Display some statistics
            $this->displayStats();

        } catch (\Exception $e) {
            $this->error('❌ Failed to generate API documentation: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Display documentation statistics
     */
    private function displayStats()
    {
        $controllers = [
            'AuthApiController' => 'Authentication',
            'AssessmentSessionApiController' => 'Assessment Sessions',
            'AssessmentResultApiController' => 'Assessment Results',
            'CommunityTemplateApiController' => 'Community Templates',
            'IntelligenceApiController' => 'Intelligence & Analytics',
            'ComplianceApiController' => 'Compliance Workspace',
            'KnowledgeBaseApiController' => 'Knowledge Base',
            'ProfileApiController' => 'User Profile',
            'WebhookApiController' => 'Webhooks'
        ];

        $this->line('📊 <comment>API Documentation Statistics:</comment>');
        $this->line('');
        
        foreach ($controllers as $controller => $description) {
            $this->line("   • {$description}");
        }

        $this->line('');
        $this->line('🔧 <comment>Available endpoints:</comment>');
        $this->line('   • Authentication: 5 endpoints');
        $this->line('   • Assessment Sessions: 7 endpoints');
        $this->line('   • Assessment Results: 4 endpoints');
        $this->line('   • Community Templates: 7 endpoints');
        $this->line('   • Intelligence & Analytics: 6 endpoints');
        $this->line('   • Compliance Workspace: 4 endpoints');
        $this->line('   • Knowledge Base: 6 endpoints');
        $this->line('   • User Profile: 3 endpoints');
        $this->line('   • Webhooks: 4 endpoints');
        $this->line('');
        $this->line('📈 <comment>Total: 46+ API endpoints documented</comment>');
    }
}