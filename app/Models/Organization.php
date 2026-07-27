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
     * Standard business sector categories.
     */
    public static function getBusinessSectors(): array
    {
        return [
            'Banking & Financial Services',
            'Government & Public Sector',
            'Technology & Software',
            'Telecommunications',
            'Healthcare & Pharmaceuticals',
            'Education & Research',
            'Manufacturing & Industry',
            'Energy, Oil & Mining',
            'Retail & E-Commerce',
            'Transportation & Logistics',
            'Media & Entertainment',
            'Professional & Consulting Services',
            'Other'
        ];
    }

    /**
     * Get the sessions associated with this organization.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(AssessmentSession::class, 'organization_id');
    }
}
