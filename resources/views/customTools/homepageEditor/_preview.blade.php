@extends('layouts.app')

@section('content')

<h2>Preview Homepage</h2>

<div style="border:1px solid #ccc; padding:10px">

    <img src="/images/mock/header.jpg" style="width:100%">

    @include('frontend.homepage', ['data' => $data])

    <img src="/images/mock/footer.jpg" style="width:100%">

</div>

<form method="POST" action="{{ route('marketing.homepage.publish') }}">
@csrf
<button class="btn btn-success">
    Publicar
</button>
</form>

@endsection