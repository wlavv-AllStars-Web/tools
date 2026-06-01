<div class="asd-shipping-panel">
    <div class="asd-shipping-panel-header">
        <span>{{ $title }}</span>
        <span>{{ $countries->count() }}</span>
    </div>

    @if($countries->isEmpty())
        <div class="asd-shipping-empty">{{ $empty }}</div>
    @else
        <div class="asd-shipping-list">
            <div class="asd-shipping-row asd-shipping-row-head">
                <div>País</div>
                <div>0 a 10 Kg</div>
                <div>10 a 30 Kg</div>
                <div>30+ Kg</div>
                <div></div>
            </div>
            @foreach($countries as $country)
                <form class="asd-shipping-row" method="POST" action="{{ route('data.asd_shipping.update') }}">
                    @csrf
                    <input type="hidden" name="id_country" value="{{ $country->id_country }}">
                    <input type="hidden" name="country" value="{{ $country->country }}">
                    <input type="hidden" name="status" value="{{ (int) $country->status }}">

                    <div class="asd-shipping-country">
                        <strong>{{ $country->country }}</strong>
                        <small>{{ $country->iso_code }} | ID {{ $country->id_country }}</small>
                    </div>

                    <input class="form-control form-control-sm" type="number" step="1" min="0" name="value_1" value="{{ (int) $country->value_1 }}" placeholder="0 a 10 Kg" title="0 a 10 Kg">
                    <input class="form-control form-control-sm" type="number" step="1" min="0" name="value_2" value="{{ (int) $country->value_2 }}" placeholder="10 a 30 Kg" title="10 a 30 Kg">
                    <input class="form-control form-control-sm" type="number" step="1" min="0" name="value_3" value="{{ (int) $country->value_3 }}" placeholder="30+ Kg" title="30+ Kg">

                    <button class="btn btn-sm btn-primary" type="submit" title="Guardar">
                        <i class="fa-solid fa-floppy-disk"></i>
                    </button>
                </form>
            @endforeach
        </div>
    @endif
</div>
