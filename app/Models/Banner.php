<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'nome',
        'imagem',
        'link',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

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
