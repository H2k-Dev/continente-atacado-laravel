<?php

namespace App\Filament\Pages;

use App\Data\Erp\CatalogSyncResult;
use App\Erp\Exceptions\ErpSyncException;
use App\Erp\Services\ErpCatalogSyncRunner;
use App\Models\ErpSyncLog;
use App\Support\ErpCatalogFileStorage;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Filament\Tables\Concerns\InteractsWithTable;

class SyncCatalog extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Sincronizar catálogo';

    protected static ?string $title = 'Sincronizar catálogo ERP';

    protected static ?string $slug = 'sync-catalog';

    protected static string|\UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 3;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Arquivo atual')
                    ->description('Último arquivo importado para sincronização.')
                    ->visible(fn (): bool => ErpCatalogFileStorage::exists())
                    ->components([
                        Placeholder::make('file_status')
                            ->label('Status')
                            ->content(fn (): string => $this->fileStatusText()),
                    ]),

                Section::make('Importar arquivo')
                    ->description('Envie o cargapro.txt exportado do EstoqMan (Schneider NT/PDV 8).')
                    ->components([
                        FileUpload::make('catalog_file')
                            ->label('Arquivo cargapro.txt')
                            ->disk('local')
                            ->directory('erp-uploads')
                            ->visibility('private')
                            ->maxSize(102400)
                            ->helperText('Após o envio, clique em "Importar e sincronizar" para atualizar o catálogo do site.'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ErpSyncLog::query())
            ->heading('Histórico recente')
            ->description('Últimas sincronizações executadas pelo painel ou pelo comando automático.')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('started_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ErpSyncLog::STATUS_SUCCESS => 'Sucesso',
                        ErpSyncLog::STATUS_FAILED => 'Falha',
                        ErpSyncLog::STATUS_RUNNING => 'Em andamento',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        ErpSyncLog::STATUS_SUCCESS => 'success',
                        ErpSyncLog::STATUS_FAILED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('categories_created')
                    ->label('Cat. criadas')
                    ->alignCenter(),

                TextColumn::make('categories_updated')
                    ->label('Cat. atualizadas')
                    ->alignCenter(),

                TextColumn::make('products_created')
                    ->label('Prod. criados')
                    ->alignCenter(),

                TextColumn::make('products_updated')
                    ->label('Prod. atualizados')
                    ->alignCenter(),

                TextColumn::make('products_deactivated')
                    ->label('Prod. desativados')
                    ->alignCenter(),
            ])
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Nenhuma sincronização registrada')
            ->emptyStateDescription('Importe um arquivo para iniciar o histórico.');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('sync-catalog-form')
                    ->livewireSubmitHandler('importAndSync')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment($this->getFormActionsAlignment())
                            ->fullWidth($this->hasFullWidthFormActions())
                            ->sticky($this->areFormActionsSticky())
                            ->key('sync-catalog-form-actions'),
                    ]),
                EmbeddedTable::make(),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('importAndSync')
                ->label('Importar e sincronizar')
                ->submit('importAndSync'),
            Action::make('syncExisting')
                ->label('Sincronizar arquivo atual')
                ->color('gray')
                ->action('syncExisting')
                ->visible(fn (): bool => ErpCatalogFileStorage::exists()),
        ];
    }

    public function importAndSync(): void
    {
        $uploaded = Arr::wrap($this->form->getState()['catalog_file'] ?? []);
        $relativePath = Arr::first($uploaded);

        if (blank($relativePath)) {
            Notification::make()
                ->title('Selecione um arquivo')
                ->body('Envie o cargapro.txt antes de sincronizar.')
                ->danger()
                ->send();

            return;
        }

        try {
            ErpCatalogFileStorage::importFromDisk($relativePath);
            $result = app(ErpCatalogSyncRunner::class)->syncStoredFile();
            $this->notifySuccess($result);
            $this->form->fill();
            $this->resetTable();
        } catch (ErpSyncException $e) {
            $this->notifyFailure($e->getMessage());
        }
    }

    public function syncExisting(): void
    {
        try {
            $result = app(ErpCatalogSyncRunner::class)->syncStoredFile();
            $this->notifySuccess($result);
            $this->resetTable();
        } catch (ErpSyncException $e) {
            $this->notifyFailure($e->getMessage());
        }
    }

    protected function notifySuccess(CatalogSyncResult $result): void
    {
        Notification::make()
            ->title('Sincronização concluída')
            ->body(new HtmlString($this->formatSyncSummary($result)))
            ->success()
            ->send();
    }

    protected function formatSyncSummary(CatalogSyncResult $result): string
    {
        $lines = [
            '<strong>Arquivo</strong>: ' . e($this->formatCount($result->productsProcessed)) . ' registros',
        ];

        if ($result->productsUnique !== $result->productsProcessed) {
            $lines[] = '<strong>Produtos únicos no arquivo</strong>: ' . e($this->formatCount($result->productsUnique));
        }

        $lines[] = '<strong>Categorias</strong>: ' . e($this->formatCount($result->categoriesCreated)) . ' criadas, ' . e($this->formatCount($result->categoriesUpdated)) . ' atualizadas';
        $lines[] = '<strong>Produtos</strong>: ' . e($this->formatCount($result->productsCreated)) . ' criados, ' . e($this->formatCount($result->productsUpdated)) . ' atualizados, ' . e($this->formatCount($result->productsDeactivated)) . ' desativados';

        if ($result->warnings !== []) {
            $lines[] = '<span class="text-sm">' . e(implode(' ', $result->warnings)) . '</span>';
        }

        return '<ul class="list-disc ps-4 space-y-1">' . collect($lines)->map(fn (string $line): string => "<li>{$line}</li>")->implode('') . '</ul>';
    }

    protected function formatCount(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    protected function notifyFailure(string $message): void
    {
        Notification::make()
            ->title('Falha na sincronização')
            ->body($message)
            ->danger()
            ->send();
    }

    protected function fileStatusText(): string
    {
        return sprintf(
            "Arquivo: %s\nÚltima atualização: %s\nTamanho: %s",
            ErpCatalogFileStorage::productsFilePath(),
            ErpCatalogFileStorage::lastModified()?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—',
            ErpCatalogFileStorage::humanSize() ?? '—',
        );
    }

    public function getTitle(): string|Htmlable
    {
        return static::$title ?? 'Sincronizar catálogo ERP';
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        return ErpSyncLog::query();
    }
}