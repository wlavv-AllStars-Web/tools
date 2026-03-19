<div id="products_container">
    <div>
        <img src="{{$products->image}}">
    </div>
    <div style="margin: 0 auto;display: inline-flex;">
        @foreach($products->products AS $product)
            <div style="margin: 20px; float: left;">
                <i class="fa-solid fa-chevron-right" style="color: dodgerblue;"></i>
                {{$product}}
            </div>
        @endforeach
    </div>
</div>
