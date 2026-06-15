<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'format' => $this->format,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'status' => $this->status,
            'error' => $this->error,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
