<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'organization_name',
        'business_sector',
        'organization_scale',
        'it_governance_structure',
        'isms_scope',
        'organization_description',
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isProfileComplete(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !empty($this->organization_name) &&
               !empty($this->business_sector) &&
               !empty($this->organization_scale) &&
               !empty($this->it_governance_structure) &&
               !empty($this->isms_scope) &&
               !empty($this->organization_description);
    }

    /**
     * Get all assessment sessions for this user
     */
    public function assessmentSessions()
    {
        return $this->hasMany(AssessmentSession::class);
    }

    /**
     * Get all community templates created by this user
     */
    public function communityTemplates()
    {
        return $this->hasMany(CommunityTemplate::class);
    }

    /**
     * Get all audit trail entries for this user
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }
}
