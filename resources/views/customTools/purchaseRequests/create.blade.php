@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="navbar navbar-light customPanel">
                <div class="container">
                    <form method="POST" action="{{ route('quote.store') }}" style="width: 100%;">
                        @csrf
                        <div class="mb-3">
                            <label>Customer contact</label>
                            <input type="text" name="customer_contact" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Language</label>
                            <select name="language" class="form-control">
                                <option value="EN">EN</option>
                                <option value="ES">ES</option>
                                <option value="FR">FR</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Customer Message</label>
                            <textarea name="customer_message" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Request</label>
                            <input type="text" name="request" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Link</label>
                            <input type="text" name="link" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Reference</label>
                            <input type="text" name="reference" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Store</label>
                            <select name="store" class="form-control">
                                <option value="ASM">ASM</option>
                                <option value="ASD">ASD</option>
                            </select>
                        </div>
                        <button class="btn btn-success">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
