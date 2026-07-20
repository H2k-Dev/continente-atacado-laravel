<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

class Cart
{
    protected const KEY = 'cart';

    public function __construct(protected Session $session) {}

    /**
     * Raw items kept in session: [product_id => ['quantidade' => int, 'observacao' => ?string]]
     */
    public function raw(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function add(int $productId, int $quantidade = 1): void
    {
        $items = $this->raw();
        $items[$productId] = [
            'quantidade' => 1,
            'observacao' => $items[$productId]['observacao'] ?? null,
        ];
        $this->persist($items);
    }

    public function setQuantity(int $productId, int $quantidade): void
    {
        $items = $this->raw();

        if ($quantidade <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = [
                'quantidade' => $quantidade,
                'observacao' => $items[$productId]['observacao'] ?? null,
            ];
        }

        $this->persist($items);
    }

    public function setObservacao(int $productId, ?string $observacao): void
    {
        $items = $this->raw();
        if (isset($items[$productId])) {
            $items[$productId]['observacao'] = $observacao;
            $this->persist($items);
        }
    }

    public function remove(int $productId): void
    {
        $items = $this->raw();
        unset($items[$productId]);
        $this->persist($items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    public function count(): int
    {
        return collect($this->raw())->sum('quantidade');
    }

    public function isEmpty(): bool
    {
        return count($this->raw()) === 0;
    }

    public function has(int $productId): bool
    {
        return isset($this->raw()[$productId]);
    }

    public function quantityFor(int $productId): int
    {
        return $this->raw()[$productId]['quantidade'] ?? 0;
    }

    /**
     * Hydrated line items with their Product model.
     *
     * @return Collection<int, array{product: Product, quantidade: int, observacao: ?string}>
     */
    public function lines(): Collection
    {
        $items = $this->raw();

        if (empty($items)) {
            return collect();
        }

        $products = Product::with('category')
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        return collect($items)
            ->filter(fn ($data, $id) => $products->has($id))
            ->map(fn ($data, $id) => [
                'product' => $products->get($id),
                'quantidade' => $data['quantidade'],
                'observacao' => $data['observacao'] ?? null,
            ])
            ->values();
    }

    protected function persist(array $items): void
    {
        $this->session->put(self::KEY, $items);
    }
}
