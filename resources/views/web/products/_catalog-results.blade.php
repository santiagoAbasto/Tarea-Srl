@if($products->count() > 0)
    <div class="products-catalog__grid">
        @foreach($products as $product)
            @php
                $eyebrowClass = match($product->subfamily?->slug) {
                    'elementos-de-cadena' => 'products-catalog__eyebrow--chain',
                    'preensamblado' => 'products-catalog__eyebrow--preassembled',
                    'rienda' => 'products-catalog__eyebrow--rienda',
                    'red-compacta' => 'products-catalog__eyebrow--compact',
                    'montajes-de-aisladores' => 'products-catalog__eyebrow--insulators',
                    'componentes-hilo-guardia-pat' => 'products-catalog__eyebrow--pat',
                    default => '',
                };

                $eyebrowColor = match($product->subfamily?->slug) {
                    'elementos-de-cadena' => '#8BCFDE',
                    'componentes-hilo-guardia-pat' => '#A46DA9',
                    'preensamblado' => '#52B6AC',
                    'rienda' => '#E86428',
                    'red-compacta' => '#9DC66C',
                    'montajes-de-aisladores' => '#E85556',
                    default => ($product->subfamily?->accent_color ?: '#8BCFDE'),
                };

                $nameClass = $product->subfamily?->slug === 'componentes-hilo-guardia-pat'
                    ? 'products-catalog__tile-name--pat'
                    : '';
            @endphp
            <a href="{{ route('web.products.show', [$product->family->slug, $product->slug]) }}" class="products-catalog__tile-link">
                <article class="products-catalog__tile">
                    <div class="products-catalog__tile-media">
                        <span class="products-catalog__tile-plus" aria-hidden="true"></span>
                        @if($product->mainMedia)
                            <img src="{{ media_asset_url($product->mainMedia) }}" alt="{{ $product->name }}">
                        @endif
                    </div>
                    <div class="products-catalog__tile-body">
                        <span class="products-catalog__eyebrow {{ $eyebrowClass }}" style="color: {{ $eyebrowColor }};">
                            {{ $product->subfamily?->name ?? $product->family?->name }}
                        </span>
                        <h2 class="{{ $nameClass }}">{{ $product->name }}</h2>
                    </div>
                </article>
            </a>
        @endforeach
    </div>

    @if($products->hasPages())
        <div class="products-catalog__pagination">
            {{ $products->links('pagination::default') }}
        </div>
    @endif
@else
    <div class="products-catalog__empty {{ $mode === 'all' ? 'products-catalog__empty--all' : '' }}">
        <h2>No hay productos disponibles</h2>
        <p>Por el momento esta categoría no tiene productos cargados. Podés incorporarlos desde el panel de administración cuando estén listos.</p>
    </div>
@endif
