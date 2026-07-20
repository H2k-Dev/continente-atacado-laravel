<?php

namespace App\Data\Erp;

readonly class ErpProductData
{
    public function __construct(
        public string $externalId,
        public string $nome,
        public ?string $categoryExternalId = null,
        public ?string $codigo = null,
        public ?string $barcode = null,
        public ?string $descricao = null,
        public ?string $unidade = null,
        public ?string $marca = null,
        public ?string $imagemUrl = null,
        public ?float $preco = null,
        public bool $ativo = true,
        public int $ordem = 0,
    ) {}
}