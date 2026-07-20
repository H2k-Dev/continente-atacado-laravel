<?php

namespace App\Erp\Connectors;

use App\Contracts\Erp\ErpCatalogConnector;

/**
 * Default connector until a real ERP is wired up.
 */
class NullErpConnector implements ErpCatalogConnector
{
    public function fetchCategories(): array
    {
        return [];
    }

    public function fetchProducts(): array
    {
        return [];
    }
}