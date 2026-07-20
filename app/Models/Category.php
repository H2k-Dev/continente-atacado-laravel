<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'erp_source',
        'erp_external_id',
        'nome',
        'slug',
        'descricao',
        'imagem',
        'ordem',
        'ativo',
        'synced_at',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'synced_at' => 'datetime',
    ];

    public function isFromErp(): bool
    {
        return filled($this->erp_source) && filled($this->erp_external_id);
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug) && filled($category->nome)) {
                $category->slug = Str::slug($category->nome);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function produtosAtivos(): HasMany
    {
        return $this->products()->where('ativo', true)->orderBy('ordem')->orderBy('nome');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
