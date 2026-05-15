<div id="sales_history">
    <a class="btn btn-success" href="{{ $url }}" style="width: 100%; margin-top: 10px;" download="sold.csv">DOWNLOAD</a>
    <table class="table table-striped table-bordered table-hover" border="1" style="width: 100%; text-align: center; display:none;">
        <tr>
            <td>{{ __('tags.reference') }}</td>
            <td>{{ __('tags.attribute reference') }}</td>
            <td>{{ __('tags.stock') }}</td>
            <td>{{ __('tags.sold') }}</td>
        </tr>
        @foreach($sold AS $data)
        <tr>
            <td>{{ $data['reference'] }}</td>
            <td>{{ $data['reference_attr'] }}</td>
            <td>{{ $data['stock'] }}</td>
            <td>{{ $data['sold'] }}</td>
        </tr>
        @endforeach
    </table>
</div>