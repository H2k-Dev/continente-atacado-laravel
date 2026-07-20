<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_source',
        'erp_external_id',
        'category_id',
        'nome',
        'slug',
        'codigo',
        'barcode',
        'descricao',
        'imagem',
        'unidade',
        'marca',
        'preco',
        'ativo',
        'destaque',
        'ordem',
        'synced_at',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'destaque' => 'boolean',
        'ordem' => 'integer',
        'preco' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function isFromErp(): bool
    {
        return filled($this->erp_source) && filled($this->erp_external_id);
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug) && filled($product->nome)) {
                $product->slug = Str::slug($product->nome);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function imagemUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (blank($this->imagem)) {
                return null;
            }

            if (Storage::disk('public')->exists($this->imagem)) {
                return '/storage/' . ltrim($this->imagem, '/');
            }

            return null;
        });
    }
}
