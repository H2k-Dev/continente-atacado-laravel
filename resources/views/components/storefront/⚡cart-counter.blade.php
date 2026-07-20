<?php

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public int $total = 0;

    public function mount(Cart $cart): void
    {
        $this->total = $cart->count();
    }

    #[On('cart-updated')]
    public function atualizar(Cart $cart): void
    {
        $this->total = $cart->count();
    }
}; ?>

<span>{{ $total }}</span>