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
            'id'         => $this->id,
            'type'       => $this->type,
            'format'     => $this->format,
            'file_name'  => $this->file_name,
            'status'     => $this->status,
            'error'      => $this->error,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
