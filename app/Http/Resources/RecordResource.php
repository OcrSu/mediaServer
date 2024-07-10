<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'attributes' => [
                'name' => $this->name,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
            ],
            'relationships' => [
                'user_id' => (string)$this->user->id,
                'user_name' => $this->user->name,
                'video_id' => (string)$this->video->id,
                'video_name' => $this->video->name,
                'folder_id' => $this->video->folder_id,
                'folder_name' => $this->video->folder->name
            ]
        ];
    }
}
