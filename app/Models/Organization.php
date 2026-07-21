<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'business_sector',
        'organization_scale',
        'it_governance_structure',
        'isms_scope',
        'address',
        'contact_email',
        'contact_phone'
    ];

    /**
     * Get the sessions associated with this organization.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AssessmentSession::class, 'organization_id');
    }
}
