@extends('layouts.app')

@section('content')

<div class="container">

<div class="block">
    <div class="block-title">HEADER (FIXO)</div>
</div>

@foreach($half as $item)
<div class="block">
    <img src="{{ $item->image_mobile ?? $item->image_en }}" style="width:100%">
</div>
@endforeach

@foreach($third as $item)
<div class="block">
    <img src="{{ $item->image_mobile ?? $item->image_en }}" style="width:100%">
</div>
@endforeach

@foreach($videos as $item)
<div class="block">
    <input value="{{ $item->youtube_code }}">
</div>
@endforeach

<div class="block">
    FOOTER
</div>

</div>

@endsection