<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;

    public const STATUSES = [
        'novo' => 'Novo',
        'em_andamento' => 'Em andamento',
        'respondido' => 'Respondido',
        'fechado' => 'Fechado',
    ];

    protected $fillable = [
        'user_id',
        'numero',
        'cliente_nome',
        'empresa',
        'email',
        'telefone',
        'cidade',
        'mensagem',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quote $quote) {
            if (blank($quote->numero)) {
                $quote->numero = static::gerarNumero();
            }
        });
    }

    public static function gerarNumero(): string
    {
        $ano = now()->year;
        $sequencial = static::whereYear('created_at', $ano)->count() + 1;

        return sprintf('ORC-%d-%04d', $ano, $sequencial);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
