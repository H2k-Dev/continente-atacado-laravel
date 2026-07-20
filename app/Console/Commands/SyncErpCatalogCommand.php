<?php

namespace App\Console\Commands;

use App\Erp\Exceptions\ErpSyncException;
use App\Erp\Services\CatalogSyncService;
use Illuminate\Console\Command;

class SyncErpCatalogCommand extends Command
{
    protected $signature = 'erp:sync-catalog';

    protected $description = 'Sincroniza categorias e produtos a partir do ERP configurado';

    public function handle(CatalogSyncService $syncService): int
    {
        $this->info('Iniciando sincronização do catálogo ERP...');

        try {
            $result = $syncService->sync();
        } catch (ErpSyncException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Categorias criadas', $result->categoriesCreated],
                ['Categorias atualizadas', $result->categoriesUpdated],
                ['Produtos criados', $result->productsCreated],
                ['Produtos atualizados', $result->productsUpdated],
                ['Produtos desativados', $result->productsDeactivated],
            ],
        );

        foreach ($result->warnings as $warning) {
            $this->warn($warning);
        }

        $this->info('Sincronização concluída.');

        return self::SUCCESS;
    }
}