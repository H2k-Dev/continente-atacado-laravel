<?php

namespace App\Contracts\Erp;

use App\Data\Erp\ErpCategoryData;
use App\Data\Erp\ErpProductData;

/**
 * Abstraction over any ERP catalog API / export.
 *
 * Each ERP (Bling, Omie, Tiny, SAP, custom REST, etc.) gets its own
 * implementation that normalizes data into ErpCategoryData / ErpProductData.
 */
interface ErpCatalogConnector
{
    /**
     * @return list<ErpCategoryData>
     */
    public function fetchCategories(): array;

    /**
     * @return list<ErpProductData>
     */
    public function fetchProducts(): array;
}