<?php

namespace App\Data\Erp;

readonly class CatalogSyncResult
{
    public function __construct(
        public int $categoriesCreated = 0,
        public int $categoriesUpdated = 0,
        public int $productsCreated = 0,
        public int $productsUpdated = 0,
        public int $productsDeactivated = 0,
        public int $productsProcessed = 0,
        public int $productsUnique = 0,
        public array $warnings = [],
    ) {}

    public function mergeCounts(self $other): self
    {
        return new self(
            categoriesCreated: $this->categoriesCreated + $other->categoriesCreated,
            categoriesUpdated: $this->categoriesUpdated + $other->categoriesUpdated,
            productsCreated: $this->productsCreated + $other->productsCreated,
            productsUpdated: $this->productsUpdated + $other->productsUpdated,
            productsDeactivated: $this->productsDeactivated + $other->productsDeactivated,
            productsProcessed: $this->productsProcessed + $other->productsProcessed,
            productsUnique: $this->productsUnique + $other->productsUnique,
            warnings: array_merge($this->warnings, $other->warnings),
        );
    }
}