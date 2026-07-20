<?php

namespace App\Erp\Connectors;

use App\Contracts\Erp\ErpCatalogConnector;
use App\Data\Erp\ErpCategoryData;
use App\Data\Erp\ErpProductData;
use App\Erp\Exceptions\ErpSyncException;
use App\Erp\Parsers\EstoqManFileParser;
use Illuminate\Support\Facades\File;

/**
 * Reads product catalog from EstoqMan retaguarda export (cargapro.txt).
 *
 * @see NT/PDV 8 — Integrando sistemas com o EstoqMan PDV (Schneider Sistemas)
 */
class EstoqManErpConnector implements ErpCatalogConnector
{
    public function __construct(
        protected EstoqManFileParser $parser,
        protected string $productsFile,
    ) {}

    public function fetchCategories(): array
    {
        $setores = [];

        foreach ($this->parseProducts() as $product) {
            $setor = trim($product['setor'] ?? '');

            if ($setor !== '') {
                $setores[$setor] = $setor;
            }
        }

        $categories = [];
        $index = 0;

        foreach (array_values($setores) as $setor) {
            $categories[] = new ErpCategoryData(
                externalId: $this->setorExternalId($setor),
                nome: $this->formatSetorNome($setor),
                ordem: $index++,
            );
        }

        return $categories;
    }

    public function fetchProducts(): array
    {
        return array_map(
            fn (array $row) => $this->mapProduct($row),
            $this->parseProducts(),
        );
    }

    /**
     * @return list<array<string, string>>
     */
    protected function parseProducts(): array
    {
        if (! File::exists($this->productsFile)) {
            throw new ErpSyncException("Arquivo de produtos EstoqMan não encontrado: {$this->productsFile}");
        }

        $contents = $this->normalizeEncoding(File::get($this->productsFile));

        return $this->parser->parseRecords($contents, 'produto');
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function mapProduct(array $row): ErpProductData
    {
        $externalId = $this->productExternalId($row);
        $setor = trim($row['setor'] ?? '');

        return new ErpProductData(
            externalId: $externalId,
            nome: trim($row['nome'] ?? $row['descricao'] ?? 'Produto sem nome'),
            categoryExternalId: $setor !== '' ? $this->setorExternalId($setor) : null,
            codigo: $this->productCodigo($row),
            barcode: $this->productBarcode($row),
            unidade: $this->nullable($row['unidade'] ?? null),
            preco: $this->parseMoney($row['preco_venda'] ?? null),
            ativo: $this->parseAtivo($row),
        );
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function productExternalId(array $row): string
    {
        $referencia = trim($row['referencia'] ?? '');
        $codigoBarras = trim($row['codigo_barras'] ?? '');

        if ($codigoBarras !== '') {
            return 'ean:' . $codigoBarras;
        }

        if ($referencia !== '') {
            return 'ref:' . $referencia;
        }

        throw new ErpSyncException('Produto EstoqMan sem referencia ou codigo_barras.');
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function productCodigo(array $row): ?string
    {
        return $this->nullable($row['referencia'] ?? null);
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function productBarcode(array $row): ?string
    {
        return $this->nullable($row['codigo_barras'] ?? null);
    }

    protected function setorExternalId(string $setor): string
    {
        return 'setor:' . preg_replace('/[^a-zA-Z0-9_-]/', '', $setor);
    }

    protected function formatSetorNome(string $setor): string
    {
        if (ctype_digit($setor)) {
            return 'Setor ' . str_pad($setor, 2, '0', STR_PAD_LEFT);
        }

        return $setor;
    }

    protected function normalizeEncoding(string $contents): string
    {
        if (mb_check_encoding($contents, 'UTF-8')) {
            return $contents;
        }

        $converted = @mb_convert_encoding($contents, 'UTF-8', 'Windows-1252');

        return $converted !== false ? $converted : $contents;
    }

    protected function parseMoney(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return ((int) preg_replace('/\D/', '', $value)) / 100;
    }

    /**
     * @param  array<string, string>  $row
     */
    protected function parseAtivo(array $row): bool
    {
        if (isset($row['inativo'])) {
            return ! in_array(strtolower(trim($row['inativo'])), ['s', 'sim', '1', 'true'], true);
        }

        $flag = strtolower(trim($row['ativo'] ?? $row['status'] ?? '1'));

        return ! in_array($flag, ['0', 'n', 'nao', 'não', 'inativo', 'false'], true);
    }

    protected function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}