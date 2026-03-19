@extends('layouts.app')

@section('content')

    <form method="POST" action="{{ route('quote.update', $purchaseRequest) }}" style="width: 100%;">
    
        <div class="row">
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    <div class="mb-3">
                        <label>Customer Contact</label>
                        <input type="text" name="customer_contact" class="form-control" value="{{ $purchaseRequest->customer_contact }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Language</label>
                        <select name="language" class="form-control">
                            <option value="EN" {{ $purchaseRequest->language=='EN'?'selected':'' }}>EN</option>
                            <option value="ES" {{ $purchaseRequest->language=='ES'?'selected':'' }}>ES</option>
                            <option value="FR" {{ $purchaseRequest->language=='FR'?'selected':'' }}>FR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Customer Message</label>
                        <textarea name="customer_message" class="form-control">{{ $purchaseRequest->customer_message }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Request</label>
                        <input type="text" name="request" class="form-control" value="{{ $purchaseRequest->request }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Link</label>
                        <input type="text" name="link" class="form-control" value="{{ $purchaseRequest->link }}">
                    </div>
                    <div class="mb-3">
                        <label>Reference</label>
                        <input type="text" name="reference" class="form-control" value="{{ $purchaseRequest->reference }}">
                    </div>
                    <div class="mb-3">
                        <label>Store</label>
                        <select name="store" class="form-control">
                            <option value="ASM" {{ $purchaseRequest->store=='ASM'?'selected':'' }}>ASM</option>
                            <option value="ASD" {{ $purchaseRequest->store=='ASD'?'selected':'' }}>ASD</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    @csrf
                    @method('PUT')
                    @if(in_array($purchaseRequest->status, ['new','waiting_supplier']))
                        <div class="mb-3">
                            <label>Change Status</label>
                            <select name="status" class="form-control">
                                @if($purchaseRequest->status=='new')
                                    <option value="waiting_supplier">Waiting Supplier</option>
                                @endif
                                <option value="quoted">Quoted</option>
                            </select>
                        </div>
                    @endif
                    @if( ( $purchaseRequest->status=='quoted' ) || ( $purchaseRequest->status=='client_notified' ) )
                        <div class="mb-3">
                            <label>Change Status</label>
                            <select name="status" class="form-control">
                                <option value="client_notified">Client Notified</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label>Supplier Price</label>
                        <input type="text" name="supplier_price" class="form-control" value="{{ $purchaseRequest->supplier_price }}">
                    </div>
                    <div class="mb-3">
                        <label>Supplier Notes</label>
                        <textarea name="supplier_notes" class="form-control">{{ $purchaseRequest->supplier_notes }}</textarea>
                    </div>   
                    <div class="mb-3">
                        <label>Lead</label>
                        <input type="text" name="sales_lead" class="form-control" value="{{ $purchaseRequest->sales_lead }}">
                    </div>
                    <div class="mb-3">
                        <label>2º Contact date</label>
                        <input type="text" name="second_contact_date" class="form-control" value="@if( strlen( $purchaseRequest->second_contact_date ) > 0) {{ \Carbon\Carbon::parse($purchaseRequest->second_contact_date)->format('d/m/Y') }} @endif">
                    </div>
                    <div class="mb-3">
                        <label>Customer Response</label>
                        <input type="text" name="customer_response" class="form-control" value="{{ $purchaseRequest->customer_response }}">
                    </div>
                    <button class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </form>
@endsection
