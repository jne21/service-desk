<?php

namespace App\Http\Resources;

use App\Models\Department;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketChangeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'event' => $this->event,
            'event_label' => $this->eventLabel($this->event),

            'actor' => $this->actor(),

            'fields' => $this->formatChanges($this->changes ?? []),

            'created_at' => $this->created_at?->toISOString(),
            'created_at_label' => $this->created_at?->format('d.m.Y H:i'),
        ];
    }

    private function eventLabel(string $event): string
    {
        return match ($event) {
            'created' => 'Створено',
            'updated' => 'Оновлено',
            'deleted' => 'Видалено',
            'restored' => 'Відновлено',
            default => $event,
        };
    }

    private function actor(): array
    {
        if ($this->user) {
            return [
                'type' => 'user',
                'label' => $this->user->name,
            ];
        }

        if ($this->source) {
            return [
                'type' => 'source',
                'label' => 'Імпорт: ' . $this->source->name,
            ];
        }

        return [
            'type' => 'system',
            'label' => 'Система',
        ];
    }

    private function formatChanges(array $changes): array
    {
        $result = [];

        foreach ($changes as $field => $values) {
            $result[] = [
                'field' => $field,
                'label' => $this->fieldLabel($field),
                'old' => $this->formatValue($field, $values['old'] ?? null),
                'new' => $this->formatValue($field, $values['new'] ?? null),
            ];
        }

        return $result;
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'title' => 'Назва',
            'description' => 'Опис',
            'status_id' => 'Статус',
            'department_id' => 'Відділ',
            default => $field,
        };
    }

    private function formatValue(string $field, mixed $value): array
    {
        if ($value === null || $value === '') {
            return [
                'raw' => $value,
                'label' => '—',
            ];
        }

        return match ($field) {
            'status_id' => [
                'raw' => $value,
                'label' => $this->statusName($value),
            ],

            'department_id' => [
                'raw' => $value,
                'label' => $this->departmentName($value),
            ],

            default => [
                'raw' => $value,
                'label' => (string) $value,
            ],
        };
    }

    private function statusName(mixed $id): string
    {
        $status = TicketStatus::orderedCached()
            ->firstWhere('id', (int) $id);

        return $status?->name ?? (string) $id;
    }

    private function departmentName(mixed $id): string
    {
        $department = Department::orderedCached()
            ->firstWhere('id', (int) $id);

        return $department?->name ?? (string) $id;
    }
}