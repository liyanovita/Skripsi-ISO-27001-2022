<?php

namespace App\Services\Notification\Templates;

use Illuminate\Support\Facades\Cache;

/**
 * Template Renderer
 * 
 * Handles loading and rendering notification templates with variable replacement.
 * Supports caching for better performance.
 */
class TemplateRenderer
{
    /**
     * Render a template with provided data
     *
     * @param string $templateName Template name (without .php extension)
     * @param string $channel Channel name (telegram, email, etc.)
     * @param array $data Data to replace variables in template
     * @return array Template content with 'subject' and 'body'
     * @throws \Exception If template not found
     */
    public function render(string $templateName, string $channel, array $data): array
    {
        $template = $this->loadTemplate($templateName, $channel);
        
        return [
            'subject' => $this->replaceVariables($template['subject'] ?? '', $data),
            'body' => $this->replaceVariables($template['body'] ?? '', $data),
        ];
    }

    /**
     * Load template from file
     *
     * @param string $templateName Template name
     * @param string $channel Channel name
     * @return array Template content
     * @throws \Exception If template not found
     */
    protected function loadTemplate(string $templateName, string $channel): array
    {
        $cacheEnabled = config('notifications.templates.cache', true);
        $cacheTtl = config('notifications.templates.cache_ttl', 3600);
        $cacheKey = "notification.template.{$templateName}.{$channel}";

        if ($cacheEnabled) {
            return Cache::remember($cacheKey, $cacheTtl, function () use ($templateName, $channel) {
                return $this->loadTemplateFile($templateName, $channel);
            });
        }

        return $this->loadTemplateFile($templateName, $channel);
    }

    /**
     * Load template file from disk
     *
     * @param string $templateName Template name
     * @param string $channel Channel name
     * @return array Template content
     * @throws \Exception If template not found
     */
    protected function loadTemplateFile(string $templateName, string $channel): array
    {
        $templatePath = config('notifications.templates.path');
        $filePath = "{$templatePath}/{$templateName}.php";

        if (!file_exists($filePath)) {
            throw new \Exception("Template not found: {$templateName}");
        }

        $templates = require $filePath;

        if (!isset($templates[$channel])) {
            throw new \Exception("Channel '{$channel}' not found in template '{$templateName}'");
        }

        return $templates[$channel];
    }

    /**
     * Replace variables in template with actual data
     *
     * Variables are in format: {variable_name}
     *
     * @param string $content Template content with variables
     * @param array $data Data to replace variables
     * @return string Content with variables replaced
     */
    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            // Convert value to string if it's not already
            $stringValue = is_scalar($value) ? (string) $value : json_encode($value);
            
            // Replace {key} with value
            $content = str_replace("{{$key}}", $stringValue, $content);
        }

        return $content;
    }

    /**
     * Clear template cache
     *
     * @param string|null $templateName Optional specific template to clear
     * @return void
     */
    public function clearCache(?string $templateName = null): void
    {
        if ($templateName) {
            // Clear specific template for all channels
            $channels = ['telegram', 'email', 'sms'];
            foreach ($channels as $channel) {
                Cache::forget("notification.template.{$templateName}.{$channel}");
            }
        } else {
            // Clear all template caches
            Cache::flush();
        }
    }
}
