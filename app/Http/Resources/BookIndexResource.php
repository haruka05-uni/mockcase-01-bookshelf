<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'average_rating' => (float) $this->reviews_avg_rating,
            'review_count' => $this->reviews_count,
        ];
    }
}
