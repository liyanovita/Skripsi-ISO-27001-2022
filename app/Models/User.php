<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Field yang dapat diisi secara massal.
     * Role dihapus agar sistem benar-benar terbuka untuk self-assessment.
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
        'organization_id',
        'job_title',
        'role_description',
        'provider',
        'provider_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        // Superadmin role has been removed — admin is now the highest privilege role.
        return false;
    }

    public function hasAdminAccess(): bool
    {
        return $this->role === 'admin';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isProfileComplete(): bool
    {
        if ($this->hasAdminAccess()) {
            return true;
        }

        return $this->organization_id !== null &&
               $this->organization &&
               !empty($this->organization->name) &&
               !empty($this->organization->business_sector) &&
               !empty($this->organization->organization_scale) &&
               !empty($this->organization->it_governance_structure) &&
               !empty($this->organization->isms_scope);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all assessment sessions for this user
     */
    public function assessmentSessions()
    {
        return $this->hasMany(AssessmentSession::class);
    }



    /**
     * Get all audit trail entries for this user
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    /**
     * Get all sessions where this user is invited as a collaborator
     */
    public function invitedSessions(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentSession::class, 'assessment_session_users', 'user_id', 'session_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Send password reset notification using custom AuditGuard email template
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
