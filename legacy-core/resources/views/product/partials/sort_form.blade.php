<section class="filter-wrapper" style="height:auto !important;">
    <div class="search-bar">
        <button class="mag"><i class="lnr lnr-magnifier"></i></button>
        <div class="search-wrapper">
            <form method="get" action="{{ url()->full() }}">
                {{ csrf_field() }}
                <input type="text" placeholder="Search our store" name="searchTerm">
            </form>
        </div>
    </div>
    <div class="sort-wrapper">
        <form action="{{ url()->full() }}" method="get" name="sort">
            {{ csrf_field() }}
            @foreach ($store->queryStrs as $key => $value)
                <input type="hidden" name="{{$key}}" value="{{$value}}">
            @endforeach
            <select
            ng-class="{filter: filter}"
            ng-mouseleave="filter = false"
            name="sortBy"
            onchange="document.sort.submit()">
                @foreach($store->sortOptions as $key => $option)
                    <option value="{{ $key }}" @if($store->selectedValue == $key) selected @endif>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        </form>
        <span><i class="lnr lnr-chevron-down"></i></span>
   </div>
</section>
