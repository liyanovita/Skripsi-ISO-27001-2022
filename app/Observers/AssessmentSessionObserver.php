<?php

namespace App\Observers;

use App\Models\AssessmentSession;
use App\Services\Logging\AuditLogger;

/**
 * Assessment Session Observer
 * 
 * Logs all changes to assessment sessions
 */
class AssessmentSessionObserver
{
    /**
     * Handle the AssessmentSession "created" event.
     */
    public function created(AssessmentSession $session): void
    {
        AuditLogger::logAction(
            'create_session',
            'AssessmentSession',
            $session->id,
            [
                'name' => $session->name,
                'status' => $session->status,
            ]
        );
    }

    /**
     * Handle the AssessmentSession "updated" event.
     */
    public function updated(AssessmentSession $session): void
    {
        $changes = $session->getChanges();

        if (!empty($changes)) {
            AuditLogger::logDataModification(
                'AssessmentSession',
                $session->id,
                'update',
                $session->getOriginal(),
                $changes
            );
        }
    }

    /**
     * Handle the AssessmentSession "deleted" event.
     */
    public function deleted(AssessmentSession $session): void
    {
        AuditLogger::logAction(
            'delete_session',
            'AssessmentSession',
            $session->id,
            [
                'name' => $session->name,
                'status' => $session->status,
            ]
        );
    }

    /**
     * Handle the AssessmentSession "restored" event.
     */
    public function restored(AssessmentSession $session): void
    {
        AuditLogger::logAction(
            'restore_session',
            'AssessmentSession',
            $session->id,
            [
                'name' => $session->name,
            ]
        );
    }

    /**
     * Handle the AssessmentSession "force deleted" event.
     */
    public function forceDeleted(AssessmentSession $session): void
    {
        AuditLogger::logAction(
            'force_delete_session',
            'AssessmentSession',
            $session->id,
            [
                'name' => $session->name,
            ]
        );
    }
}
