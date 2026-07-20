<?php

use App\Models\Catalog;
use App\Support\StorefrontAuth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::livewire('/', 'storefront.catalog')->name('home');
Route::livewire('/produto/{product:slug}', 'storefront.product-show')->name('product.show');
Route::livewire('/carrinho', 'storefront.cart')->name('cart');
Route::livewire('/catalogos', 'storefront.catalogs')->name('catalogs');
Route::get('/catalogos/{catalog}/download', function (Catalog $catalog) {
    abort_unless(Storage::disk('public')->exists($catalog->arquivo), 404);

    return Storage::disk('public')->download(
        $catalog->arquivo,
        Str::slug($catalog->nome) . '.pdf',
    );
})->name('catalogs.download');

Route::middleware('guest:'.StorefrontAuth::GUARD)->group(function () {
    Route::livewire('/entrar', 'storefront.login')->name('login');
    Route::livewire('/cadastro', 'storefront.register')->name('register');
});

Route::middleware('auth:'.StorefrontAuth::GUARD)->group(function () {
    Route::livewire('/minha-conta', 'storefront.account')->name('account');
    Route::livewire('/minha-conta/orcamentos', 'storefront.account-quotes')->name('account.quotes');

    Route::post('/sair', function () {
        StorefrontAuth::guard()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    })->name('logout');
});