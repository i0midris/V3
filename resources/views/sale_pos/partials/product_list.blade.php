<style>
/* Product List Container */
.product_list {
    padding: 0.5rem;
    margin-bottom: 1rem;
}

/* Card Main Styling */
.card.product_box {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 260px;
    direction: rtl;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.card.product_box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

/* Image Container */
.image-container {
    min-height: 140px;
    max-height: 160px;
    transition: all 0.3s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9f9f9;
    position: relative;
    overflow: hidden;
}

.image-container::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(to top, rgba(249, 249, 249, 0.8), transparent);
}

/* Card Body */
.card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 0.75rem;
    text-align: center;
    direction: rtl;
    background: white;
}

/* Product Title */
.card-title {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-size: 1.15rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    line-height: 1.4;
    color: #1a202c;
}

/* Subtitle (SKU) */
.card-subtitle {
    font-size: 0.85rem;
    color: #718096;
    margin-bottom: 0.25rem;
}

/* Small Text */
.card-body small {
    font-size: 0.8rem;
    color: #718096;
}

/* Stock Status */
.stock-status {
    margin-top: 0.25rem;
    padding: 0.25rem;
    background-color: #EDF2F7;
    border-radius: 4px;
    font-size: 0.8rem;
}

/* Price Container */
.price-container {
    margin-top: 0.5rem;
    padding: 0.4rem;
    background-color: #E7FFF7;
    border-radius: 6px;
    border: 1px solid #A7F3D0;
}

/* Badges General */
.badge {
    font-size: 0.85rem;
    padding: 0.35em 0.5em;
    margin: 0.1em;
    border-radius: 6px;
    font-weight: 500;
    display: inline-block;
}

/* Success Badge (Main Price) */
.badge.bg-success {
    background-color: #10B981 !important;
    color: white;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.4em 0.7em;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
}

/* Info Badge (Group Prices) */
.badge.bg-info {
    background-color: #3B82F6 !important;
    color: white !important;
    font-size: 0.9rem;
}

/* Empty State */
.no-products {
    text-align: center;
    padding: 2rem;
    color: #718096;
    font-size: 1.1rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card.product_box {
        min-height: 240px;
    }
    
    .image-container {
        min-height: 120px;
        max-height: 140px;
    }
    
    .card-title {
        font-size: 1rem;
    }
    
    .badge.bg-success {
        font-size: 0.9rem;
    }
}
</style>


@forelse($products as $product)
    @php
        $image_url = count($product->media) > 0
            ? $product->media->first()->display_url
            : (!empty($product->product_image)
                ? asset('/uploads/img/' . rawurlencode($product->product_image))
                : asset('/img/default.png'));
    @endphp

    @php ob_start(); @endphp
        {{ $product->name }}
        @if($product->type == 'variable')
            - {{ $product->variation }}
        @endif
        ({{ $product->sub_sku }})
        @if(!empty($show_prices))
            {{ __('lang_v1.default') }} - @format_currency($product->selling_price)
            @foreach($product->group_prices as $group_price)
                @if(array_key_exists($group_price->price_group_id, $allowed_group_prices))
                    {{ $allowed_group_prices[$group_price->price_group_id] }} - @format_currency($group_price->price_inc_tax)
                @endif
            @endforeach
        @endif
    @php
        $title = trim(preg_replace('/\s+/', ' ', strip_tags(ob_get_clean())));
    @endphp

    <div class="col-lg-3 col-md-4 col-sm-6 col-12 product_list no-print">
        <div class="card h-100 shadow-lg product_box border-0 hover:tw-shadow-2xl transition-all duration-300" 
             data-variation_id="{{ $product->id }}" title="{{ $title }}">
            
            <div class="image-container" style="
                background-image: url('{{ $image_url }}');
                background-repeat: no-repeat;
                background-position: center;
                background-size: contain;">
            </div>

            <div class="card-body text-center">
                <h6 class="card-title mb-1" style="font-size: 100%;">
                    {{ $product->name }}
                    @if($product->type == 'variable')
                        - {{ $product->variation }}
                    @endif
                </h6>
                <p class="card-subtitle text-muted mb-1" style="font-size: 85%;">{{ $product->sub_sku }}</p>
                <p class="small text-muted mb-0" style="font-size: 85%;">
                    @if($product->enable_stock)
                        {{ @num_format($product->qty_available) }} {{ $product->unit }} @lang('lang_v1.in_stock')
                    @else
                        --
                    @endif
                </p>
                @if(!empty($show_prices))
                    <div class="mt-2">
                        <span class="badge bg-success">@format_currency($product->selling_price)</span>
                        @foreach($product->group_prices as $group_price)
                            @if(array_key_exists($group_price->price_group_id, $allowed_group_prices))
                                <span class="badge bg-info text-dark">
                                    {{ $allowed_group_prices[$group_price->price_group_id] }}: @format_currency($group_price->price_inc_tax)
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <input type="hidden" id="no_products_found">
    <div class="col-md-12">
        <h4 class="text-center">@lang('lang_v1.no_products_to_display')</h4>
    </div>
@endforelse
