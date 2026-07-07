<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'source' => $this->source ? [
                'id' => $this->source->id,
                'code' => $this->source->code,
                'name' => $this->source->name,
            ] : null,

            'status' => $this->status ? [
                'id' => $this->status->id,
                'code' => $this->status->code,
                'name' => $this->status->name,
                'isFinal' => $this->status->is_final,
            ] : null,

            'ticketsCount' => $this->tickets_count,
            'createdCount' => $this->created_count,
            'updatedCount' => $this->updated_count,
            'failedCount' => $this->failed_count,

            'error' => $this->error_message,

            'startedAt' => $this->started_at?->toDateTimeString(),
            'finishedAt' => $this->finished_at?->toDateTimeString(),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
