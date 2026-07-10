<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\TicketSource;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status_id',
        'user_id',
        'department_id',
        'source_id',
        'external_id',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(TicketSource::class, 'source_id');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(TicketChange::class);
    }

    public function deleteBy(User $user): ?bool
    {
        $this->forceFill([
            'deleted_by_user_id' => $user->id,
        ])->save();

        return $this->delete();
    }

    public function scopeVisibleFor(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->department_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('department_id', $user->department_id);
    }
}
