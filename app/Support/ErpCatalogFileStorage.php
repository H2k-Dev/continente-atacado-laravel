<?php

namespace App\Support;

use App\Erp\Exceptions\ErpSyncException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ErpCatalogFileStorage
{
    public static function productsFilePath(): string
    {
        return config('erp.connectors.estoqman.products_file', storage_path('erp/cargapro.txt'));
    }

    public static function exists(): bool
    {
        return File::exists(self::productsFilePath());
    }

    public static function storeContents(string $contents): void
    {
        $path = self::productsFilePath();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    public static function importFromDisk(string $relativePath, string $disk = 'local'): void
    {
        if (! Storage::disk($disk)->exists($relativePath)) {
            throw new ErpSyncException("Arquivo enviado não encontrado: {$relativePath}");
        }

        self::storeContents(Storage::disk($disk)->get($relativePath));

        Storage::disk($disk)->delete($relativePath);
    }

    public static function lastModified(): ?Carbon
    {
        if (! self::exists()) {
            return null;
        }

        return Carbon::createFromTimestamp(File::lastModified(self::productsFilePath()));
    }

    public static function humanSize(): ?string
    {
        if (! self::exists()) {
            return null;
        }

        $bytes = File::size(self::productsFilePath());

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
}