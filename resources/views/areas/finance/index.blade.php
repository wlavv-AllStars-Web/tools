@extends('layouts.app')


@section('content')

    @include("areas.finance.includes.js")

    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-12">
                <div class="listUL" style="margin: 0 auto;display: table;">
                    @foreach($accessList AS $access)
                        <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                            <a href="{{$access['url']}}">     
                                <div>{!! $access['icon'] !!}</div>
                                <div>{{ $access['name']}}</div>
                            </a>
                        </div>
                    @endforeach

                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.refunds.index')}}">     
                            <div><i class="fa-solid fa-file-invoice-dollar" style="font-size: 40px;"></i></div>
                            <div>REFUNDS</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.vat.check')}}">     
                            <div><i class="fa-solid fa-user-check" style="font-size: 40px;"></i></div>
                            <div>VAT</div>
                        </a>
                    </div>

                </div>
            </div>
            {{--
            <div class="col-lg-6">
                <div class="row" style="border: 1px solid #ccc;padding: 5px;">
                    <div class="col-lg-12"><h5 style="margin-top:7px;text-align: center;">{{ __('tags.currency rate - 1€')}}</h5></div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">元</span>
                            </div>
                            <input class="form-control" id="yuan" name="yuan" type="text" value="{{$rates->yuan}}" placeholder="元" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">£</span>
                            </div>
                            <input class="form-control" id="pound" name="pound" type="text" value="{{$rates->pound}}" placeholder="£" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">$</span>
                            </div>
                            <input class="form-control" id="dollar" name="dollar" type="text" value="{{$rates->usd}}" placeholder="$" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="basic-addon1" style="border-radius: 5px 0 0 5px;">¥</span>
                            </div>
                            <input class="form-control" id="yen" name="yen" type="text" value="{{$rates->yen}}" placeholder="¥" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <span class="btn btn-primary" onclick="saveCurrencyRate()" style="width: 100%"> {{ __('tags.save')}} </span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <div class="listUL" style="margin: 0 auto;">
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.refunds.index')}}">     
                            <div><i class="fa-solid fa-file-invoice-dollar" style="font-size: 40px;"></i></div>
                            <div>REFUNDS</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.vat.check')}}">     
                            <div><i class="fa-solid fa-user-check" style="font-size: 40px;"></i></div>
                            <div>VAT</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
                        <a href="{{route('finance.tools.payment_links.index')}}">     
                            <div><i class="fa-solid fa-money-check-dollar" style="font-size: 40px;"></i></div>
                            <div>LINK</div>
                        </a>
                    </div>


                </div>
            </div>
            --}}
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>VATs com estado diferente de valido</strong>
            <span class="badge text-bg-danger">{{ $vatAlerts->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Estado</th><th>Loja</th><th>Company</th><th>Orders ID's</th></tr></thead>
                <tbody>
                @forelse($vatAlerts as $validation)
                    @php($statusColor=['invalid'=>'danger','missing_vat'=>'danger','manual_review'=>'danger','pending'=>'warning','processing'=>'warning','retry_scheduled'=>'warning'][$validation->status] ?? 'secondary')
                    @php($statusLabel=['pending'=>'Pendente','processing'=>'Em validacao','retry_scheduled'=>'Nova tentativa agendada','invalid'=>'Invalido','missing_vat'=>'VAT/morada em falta','manual_review'=>'Revisao manual'][$validation->status] ?? $validation->status)
                    <tr>
                        <td><span class="badge text-bg-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                        <td>@forelse($validation->orders as $link)<span class="badge text-bg-{{ $link->store === 'ASD' ? 'info' : 'danger' }} d-block mb-1">{{ $link->store }}</span>@empty<span class="text-muted">-</span>@endforelse</td>
                        <td>@forelse($validation->orders as $link)<span class="d-block">{{ $link->company ?: '-' }}</span>@empty<span class="text-muted">-</span>@endforelse</td>
                        <td>@forelse($validation->orders as $link)<span class="d-block">#{{ $link->id_order }}</span>@empty<span class="text-muted">-</span>@endforelse</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Sem VATs por validar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {!! $counters !!}
@endsection
