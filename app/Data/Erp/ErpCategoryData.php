<?php

namespace App\Data\Erp;

readonly class ErpCategoryData
{
    public function __construct(
        public string $externalId,
        public string $nome,
        public ?string $descricao = null,
        public bool $ativo = true,
        public int $ordem = 0,
    ) {}
}