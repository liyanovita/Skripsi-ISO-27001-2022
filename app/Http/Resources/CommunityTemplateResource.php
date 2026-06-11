<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'author_name' => $this->author_name,
            'tags' => $this->tags,
            'base_score' => $this->base_score,
            'usage_count' => $this->usage_count,
            'upvotes' => $this->upvotes,
            'average_rating' => $this->average_rating,
            'rating_count' => $this->rating_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include content data only when specifically requested
            'content_data' => $this->when($request->routeIs('api.community.templates.show'), $this->content_data),
        ];
    }
}