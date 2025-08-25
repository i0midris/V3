<style>
.image-container {
    border-radius: 0.5rem 0.5rem 0 0;
    min-height: 140px;
    max-height: 200px;
    transition: all 0.3s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card.product_box {
    display: flex;
    flex-direction: column;
    height: 90%; /* Ensures consistent height */
    min-height: 30%; /* Adjust this to taste */
}

.card-body {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center; /* ← centers vertically */
    align-items: center;     /* ← centers horizontally */
    padding: 1rem;
    text-align: center;      /* ← ensures text stays centered */
}


.card-title,
.card-subtitle,
.card-body small {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Optional: if you want to show longer names in 2 lines */
.card-title {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
</style>


@forelse($products as $product)
    @php
        $image_url = count($product->media) > 0
            ? $product->media->first()->display_url
            : (!empty($product->product_image)
                ? asset('/uploads/img/' . rawurlencode($product->product_image))
                : asset('/img/default.png'));

        $title = $product->name;
        if ($product->type == 'variable') {
            $title .= ' - ' . $product->variation;
        }
        $title .= ' (' . $product->sub_sku . ')';

        if (!empty($show_prices)) {
            $title .= ' ' . __('lang_v1.default') . ' - ' . @format_currency($product->selling_price);
            foreach ($product->group_prices as $group_price) {
                if (array_key_exists($group_price->price_group_id, $allowed_group_prices)) {
                    $title .= ' ' . $allowed_group_prices[$group_price->price_group_id] . ' - ' . @format_currency($group_price->price_inc_tax);
                }
            }
        }
    @endphp

    <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4 product_list no-print">
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
