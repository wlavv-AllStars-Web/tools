<table id="ordersTable" class="display" style="width:100%;">
    <thead>
        <tr>
            <th>ORDER</th>
            <th>STATUS</th>
            <th>REFERENCE</th>
            <th>CUSTOMER</th>
            <th>DESCRIPTION</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($returns AS $return)
            @foreach($return->details AS $detail)
                <tr>
                    <td>{{$return->id_order}}</td>
                    <td>
                        <span style="color: #FFF;background-color: {{$return->status->color}}; padding: 5px 10px; border-radius: 5px;">
                            {{$return->status->lang->name}}
                        </span>
                    </td>
                    <td>
                        <div style="float: left; margin-right: 5px; background-color: dodgerblue; color: #FFF; width: 25px; height: 25px; border-radius: 25px;text-align: center;">
                            {{$detail->product_quantity}}
                        </div>
                        <span>{{$detail->orderDetail->product_reference}}</span>
                    </td>
                    <td>{{$return->customer->firstname}} {{$return->customer->lastname}}</td>
                    <td>{{$detail->problem_description}}</td>
                    <td>
                        <button class="btn btn-primary open-ajax-modal" data-route="{{ route('returns.getModal', ['id' => $detail->id_order_detail]) }}">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>