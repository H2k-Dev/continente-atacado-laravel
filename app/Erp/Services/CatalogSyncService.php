<?php

namespace App\Erp\Services;

use App\Contracts\Erp\ErpCatalogConnector;
use App\Data\Erp\CatalogSyncResult;
use App\Data\Erp\ErpCategoryData;
use App\Data\Erp\ErpProductData;
use App\Erp\Exceptions\ErpSyncException;
use App\Models\Category;
use App\Models\ErpSyncLog;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogSyncService
{
    public function __construct(
        protected ErpCatalogConnector $connector,
        protected string $erpSource,
        protected bool $deactivateMissing = true,
        protected string $fallbackCategorySlug = 'sem-categoria',
    ) {}

    public function sync(): CatalogSyncResult
    {
        $log = ErpSyncLog::create([
            'erp_source' => $this->erpSource,
            'status' => ErpSyncLog::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            $result = DB::transaction(fn () => $this->performSync());

            $log->update([
                'status' => ErpSyncLog::STATUS_SUCCESS,
                'categories_created' => $result->categoriesCreated,
                'categories_updated' => $result->categoriesUpdated,
                'products_created' => $result->productsCreated,
                'products_updated' => $result->productsUpdated,
                'products_deactivated' => $result->productsDeactivated,
                'message' => $result->warnings ? implode("\n", $result->warnings) : null,
                'finished_at' => now(),
            ]);

            return $result;
        } catch (\Throwable $e) {
            $log->update([
                'status' => ErpSyncLog::STATUS_FAILED,
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw new ErpSyncException('Falha ao sincronizar catálogo do ERP: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function performSync(): CatalogSyncResult
    {
        $warnings = [];
        $categoryMap = $this->syncCategories($this->connector->fetchCategories());
        $fallbackCategory = $this->resolveFallbackCategory();

        $erpProducts = $this->connector->fetchProducts();
        $processed = count($erpProducts);
        $uniqueExternalIds = array_unique(array_map(fn (ErpProductData $product) => $product->externalId, $erpProducts));
        $unique = count($uniqueExternalIds);

        if ($unique < $processed) {
            $merged = $processed - $unique;
            $warnings[] = "Arquivo com {$processed} registros e {$unique} produtos únicos; {$merged} linhas compartilham o mesmo identificador e foram mescladas.";
        }

        $seenProductIds = [];
        $created = 0;
        $updated = 0;

        foreach ($erpProducts as $erpProduct) {
            $categoryId = $categoryMap[$erpProduct->categoryExternalId] ?? $fallbackCategory->id;

            if ($erpProduct->categoryExternalId && ! isset($categoryMap[$erpProduct->categoryExternalId])) {
                $warnings[] = "Produto {$erpProduct->externalId}: categoria ERP {$erpProduct->categoryExternalId} não encontrada; usando fallback.";
            }

            $product = $this->findProduct($erpProduct);
            $attributes = $this->productAttributes($erpProduct, $categoryId, $product);

            if ($product) {
                $product->update($attributes);
                $updated++;
            } else {
                Product::create($attributes);
                $created++;
            }

            $seenProductIds[] = $erpProduct->externalId;
        }

        $deactivated = 0;

        if ($this->deactivateMissing && $seenProductIds !== []) {
            $deactivated = Product::query()
                ->where('erp_source', $this->erpSource)
                ->whereNotIn('erp_external_id', $seenProductIds)
                ->where('ativo', true)
                ->update(['ativo' => false, 'synced_at' => now()]);
        }

        return new CatalogSyncResult(
            categoriesCreated: $categoryMap['_created'] ?? 0,
            categoriesUpdated: $categoryMap['_updated'] ?? 0,
            productsCreated: $created,
            productsUpdated: $updated,
            productsDeactivated: $deactivated,
            productsProcessed: $processed,
            productsUnique: $unique,
            warnings: $warnings,
        );
    }

    protected function findProduct(ErpProductData $erpProduct): ?Product
    {
        $product = Product::query()
            ->where('erp_source', $this->erpSource)
            ->where('erp_external_id', $erpProduct->externalId)
            ->first();

        if ($product || blank($erpProduct->barcode)) {
            return $product;
        }

        return Product::query()
            ->where('erp_source', $this->erpSource)
            ->where('barcode', $erpProduct->barcode)
            ->first();
    }

    /**
     * @param  list<ErpCategoryData>  $erpCategories
     * @return array<string, int> externalId => local id, plus _created/_updated counts
     */
    protected function syncCategories(array $erpCategories): array
    {
        $map = ['_created' => 0, '_updated' => 0];

        foreach ($erpCategories as $erpCategory) {
            $category = Category::query()
                ->where('erp_source', $this->erpSource)
                ->where('erp_external_id', $erpCategory->externalId)
                ->first();

            $attributes = [
                'erp_source' => $this->erpSource,
                'erp_external_id' => $erpCategory->externalId,
                'nome' => $erpCategory->nome,
                'slug' => $this->uniqueCategorySlug($erpCategory->nome, $category),
                'descricao' => $erpCategory->descricao,
                'ordem' => $erpCategory->ordem,
                'ativo' => $erpCategory->ativo,
                'synced_at' => now(),
            ];

            if ($category) {
                $category->update($attributes);
                $map['_updated']++;
            } else {
                $category = Category::create($attributes);
                $map['_created']++;
            }

            $map[$erpCategory->externalId] = $category->id;
        }

        return $map;
    }

    protected function resolveFallbackCategory(): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => $this->fallbackCategorySlug],
            [
                'nome' => 'Sem categoria',
                'descricao' => 'Produtos do ERP sem categoria mapeada.',
                'ativo' => false,
                'ordem' => 9999,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function productAttributes(ErpProductData $erpProduct, int $categoryId, ?Product $existing): array
    {
        return [
            'erp_source' => $this->erpSource,
            'erp_external_id' => $erpProduct->externalId,
            'category_id' => $categoryId,
            'nome' => $erpProduct->nome,
            'slug' => $this->uniqueProductSlug($erpProduct, $existing),
            'codigo' => $erpProduct->codigo,
            'barcode' => $erpProduct->barcode,
            'descricao' => $erpProduct->descricao,
            'unidade' => $erpProduct->unidade,
            'marca' => $erpProduct->marca,
            'preco' => $erpProduct->preco,
            'ativo' => $erpProduct->ativo,
            'ordem' => $erpProduct->ordem,
            'synced_at' => now(),
        ];
    }

    protected function uniqueCategorySlug(string $nome, ?Category $existing): string
    {
        $base = Str::slug($nome) ?: 'categoria';
        $slug = $base;
        $suffix = 1;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    protected function uniqueProductSlug(ErpProductData $erpProduct, ?Product $existing): string
    {
        $base = Str::slug($erpProduct->codigo ?: $erpProduct->barcode ?: $erpProduct->nome) ?: 'produto';
        $slug = $base;
        $suffix = 1;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}