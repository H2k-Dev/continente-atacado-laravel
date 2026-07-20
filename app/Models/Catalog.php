<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Catalog extends Model
{
    protected $fillable = [
        'nome',
        'arquivo',
        'capa',
    ];

    protected function arquivoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->storageUrl($this->arquivo));
    }

    protected function capaUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->storageUrl($this->capa));
    }

    private function storageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Storage::disk('public')->exists($path)) {
            return '/storage/' . ltrim($path, '/');
        }

        return null;
    }
}
