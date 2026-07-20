<?php

namespace Tests\Feature;

use App\Erp\Connectors\EstoqManErpConnector;
use App\Erp\Parsers\EstoqManFileParser;
use App\Erp\Services\CatalogSyncService;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EstoqManConnectorTest extends TestCase
{
    use RefreshDatabase;

    protected string $fixture;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = storage_path('framework/testing-cargapro.txt');

        File::put($this->fixture, <<<'TXT'
<ar|produtos>
   <rg|produto>
      <cp|codigo_barras>78908912627331</cp|codigo_barras>
      <cp|descricao>Leite integral</cp|descricao>
      <cp|preco_venda>2311</cp|preco_venda>
      <cp|unidade>CX 12UN</cp|unidade>
      <cp|setor>Bebidas</cp|setor>
      <cp|icms>1700</cp|icms>
      <cp|cst>0</cp|cst>
   </rg|produto>
   <rg|produto>
      <cp|codigo_barras>1432</cp|codigo_barras>
      <cp|referencia>REF-1432</cp|referencia>
      <cp|descricao>Batata</cp|descricao>
      <cp|preco_venda>112</cp|preco_venda>
      <cp|setor>Mercearia</cp|setor>
   </rg|produto>
</ar|produtos>
TXT);
    }

    protected function tearDown(): void
    {
        File::delete($this->fixture);

        parent::tearDown();
    }

    public function test_parser_extrai_registros_de_produto(): void
    {
        $records = (new EstoqManFileParser)->parseRecords(File::get($this->fixture), 'produto');

        $this->assertCount(2, $records);
        $this->assertSame('Leite integral', $records[0]['descricao']);
        $this->assertSame('2311', $records[0]['preco_venda']);
    }

    public function test_connector_mapeia_produtos_e_categorias(): void
    {
        $connector = new EstoqManErpConnector(new EstoqManFileParser, $this->fixture);

        $categories = $connector->fetchCategories();
        $products = $connector->fetchProducts();

        $this->assertCount(2, $categories);
        $this->assertSame('Bebidas', $categories[0]->nome);

        $this->assertSame('Leite integral', $products[0]->nome);
        $this->assertSame(23.11, $products[0]->preco);
        $this->assertSame('CX 12UN', $products[0]->unidade);
        $this->assertNull($products[0]->codigo);
        $this->assertSame('78908912627331', $products[0]->barcode);

        $this->assertSame('ean:78908912627331', $products[0]->externalId);
        $this->assertSame('REF-1432', $products[1]->codigo);
        $this->assertSame('1432', $products[1]->barcode);
        $this->assertSame('ean:1432', $products[1]->externalId);
        $this->assertSame(1.12, $products[1]->preco);
    }

    public function test_variantes_com_mesma_referencia_sincronizam_como_produtos_distintos(): void
    {
        File::put($this->fixture, <<<'TXT'
<ar|produtos>
   <rg|produto>
      <cp|codigo_barras>930712</cp|codigo_barras>
      <cp|referencia>3301</cp|referencia>
      <cp|descricao>Cadeira preta</cp|descricao>
      <cp|preco_venda>10000</cp|preco_venda>
      <cp|setor>Moveis</cp|setor>
   </rg|produto>
   <rg|produto>
      <cp|codigo_barras>930713</cp|codigo_barras>
      <cp|referencia>3301</cp|referencia>
      <cp|descricao>Cadeira branca</cp|descricao>
      <cp|preco_venda>11000</cp|preco_venda>
      <cp|setor>Moveis</cp|setor>
   </rg|produto>
</ar|produtos>
TXT);

        $connector = new EstoqManErpConnector(new EstoqManFileParser, $this->fixture);
        $service = new CatalogSyncService($connector, 'estoqman');

        $result = $service->sync();

        $this->assertSame(2, $result->productsProcessed);
        $this->assertSame(2, $result->productsUnique);
        $this->assertSame(2, $result->productsCreated);
        $this->assertSame(2, Product::count());
        $this->assertSame('3301', Product::query()->where('barcode', '930712')->value('codigo'));
        $this->assertSame('3301', Product::query()->where('barcode', '930713')->value('codigo'));
    }

    public function test_sync_completo_a_partir_do_arquivo_estoqman(): void
    {
        $connector = new EstoqManErpConnector(new EstoqManFileParser, $this->fixture);
        $service = new CatalogSyncService($connector, 'estoqman');

        $result = $service->sync();

        $this->assertSame(2, $result->categoriesCreated);
        $this->assertSame(2, $result->productsCreated);

        $leite = Product::query()->where('nome', 'Leite integral')->first();
        $this->assertSame('23.11', $leite->preco);
        $this->assertSame('78908912627331', $leite->barcode);
        $this->assertSame('Bebidas', Category::find($leite->category_id)->nome);
    }

    public function test_arquivo_real_schneider_com_tags_maiusculas(): void
    {
        $fixture = base_path('tests/fixtures/estoqman/cargapro.txt');
        $connector = new EstoqManErpConnector(new EstoqManFileParser, $fixture);
        $service = new CatalogSyncService($connector, 'estoqman');

        $result = $service->sync();

        $this->assertSame(2, $result->categoriesCreated);
        $this->assertSame(3, $result->productsCreated);

        $shampoo = Product::query()->where('barcode', '000001')->first();
        $this->assertSame('SHAMPOO AUTOMOTIVO', $shampoo->nome);
        $this->assertSame('25.00', $shampoo->preco);
        $this->assertTrue($shampoo->ativo);

        $inativo = Product::query()->where('barcode', '000003')->first();
        $this->assertFalse($inativo->ativo);

        $setor = Category::query()->where('nome', 'Setor 01')->first();
        $this->assertNotNull($setor);
        $this->assertSame('setor:1', $setor->erp_external_id);
    }
}