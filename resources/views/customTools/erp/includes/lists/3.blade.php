<div class="navbar navbar-light customPanel" style="margin: 10px; width: calc(100% - 30px);">
    <table id="ordersTable" class="display" style="width:100%;">
        <thead>
            <tr>
                <th>NAME</th>
                <th>BACKORDERS</th>
                <th>WARRANTIES</th>
                <th>COUNTRY</th>
                <th>ADDRESS</th>
                <th>POST CODE</th>
                <th>PHONE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data AS $item)
                <tr style="cursor: pointer" class="supplierRow">
                    <td> {{$item->name}} </td>
                    <td> {{$item->email}} </td>
                    <td> {{$item->email_claims}} </td>
                    <td> {{$item->address->country->lang_en->name}} </td>
                    <td> {{$item->address->address1}} {{$item->address->address2}} </td>
                    <td> {{$item->address->postcode}} </td>
                    <td> {{$item->address->phone}} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>