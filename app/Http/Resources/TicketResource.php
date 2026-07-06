<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,

            'title' => $this->title,
            'description' => $this->description,

            'status' => $this->status ? [
                'id' => $this->status->id,
                'code' => $this->status->code,
                'name' => $this->status->name,
            ] : null,

            'department' => $this->department ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null,

            'created_by' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null,

            'source' => $this->source ? [
                'id' => $this->source->id,
                'code' => $this->source->code,
                'name' => $this->source->name,
            ] : null,

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}