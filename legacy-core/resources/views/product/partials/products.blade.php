<section class="grid product">
    @foreach($store->products as $product)
        @if ($product->quantity_available <= 0)
            <div class="product-item item sold-out" id="product-top">
                    <img ng-src="{{$product->media->first()->url_lg or '' }}" class="item_image" alt="{{ $product->short_description }}">
                    <h3>SOLD OUT</h3>
                    <div class="product-info">
                        <h2>{{$product->name}}</h2>
                        @if (isset($store_owner) && $store_owner->hasSellerType(['Hybrid']))
                            <small>
                                @if ($product->corporate)
                                    Available from {{ Config::get('site.company_name') }}
                                @else
                                    <i class="dot green"></i> Available from {{ session()->get('store_owner.full_name') }}
                                @endif
                            </small>
                        @endif
                        <p>
                            {{money_format('$%i', $product->price)}}
                        </p>
                    </div>
                </a>
            </div>
        @else
            <div class="product-item item" id="product-top">
                @if ($product->corporate && isset($eventId))
                    <a href="/store/product/{{$product->slug}}?corporate=1">
                @elseif ($product->corporate)
                    <a href="/store/product/{{$product->slug}}?corporate=1">
                @elseif (isset($eventId))
                    <a href="/store/product/{{$product->slug}}?event-id={{$eventId}}">
                @else
                    <a href="/store/product/{{$product->slug}}">
                @endif
                    <img ng-src="{{$product->media->first()->url_lg or '' }}" class="item_image" alt="{{ $product->short_description }}">
                    <div class="product-info">
                        <h2>{{$product->name}}</h2>
                        @if (isset($store_owner) && $store_owner->hasSellerType(['Hybrid']))
                            <small>
                                @if ($product->corporate)
                                    Available from {{ Config::get('site.company_name') }}
                                @else
                                    <i class="dot green"></i> Available from {{ session()->get('store_owner.full_name') }}
                                @endif
                            </small>
                        @endif
                        <p>
                            {{money_format('$%i', $product->price)}}
                        </p>
                    </div>
                </a>
            </div>
        @endif
    @endforeach
</section>
