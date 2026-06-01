@php
    $processLabel = $return->process === 'warranty' ? 'Warranty' : 'Return';
    $titleClass = $acceptedAt ? ($isInsideDeadline ? 'valid' : 'invalid') : 'warning';
    $title = $acceptedAt
        ? ($isInsideDeadline ? 'RMA autorizado!' : 'RMA fora do prazo!')
        : 'RMA sem data de aceitacao!';
@endphp

<div class="rma-result">
    <h3 class="rma-result-title {{ $titleClass }}">{{ $title }}</h3>

    <div class="rma-summary">
        <div class="rma-summary-box">
            <span class="rma-summary-label">Aceite em</span>
            <span class="rma-summary-value">{{ $acceptedAt ? $acceptedAt->format('d-m-Y') : '-' }}</span>
        </div>
        <div class="rma-summary-box">
            <span class="rma-summary-label">Valido ate</span>
            <span class="rma-summary-value">{{ $deadline ? $deadline->format('d-m-Y') : '-' }}</span>
        </div>
        <div class="rma-summary-box">
            <span class="rma-summary-label">Dias</span>
            <span class="rma-summary-value">{{ $daysElapsed !== null ? $daysElapsed . '/' . $deadlineDays : '-' }}</span>
        </div>
    </div>

    <table class="rma-detail-table">
        <tr>
            <td class="td_label">TIPO RMA</td>
            <td class="td_value">{{ $processLabel }}</td>
        </tr>
        <tr>
            <td class="td_label">ID RMA</td>
            <td class="td_value">{{ $return->id_order_return }}</td>
        </tr>
        @if($details->pluck('request_reference')->filter()->isNotEmpty())
            <tr>
                <td class="td_label">RMA CODE</td>
                <td class="td_value">{{ $details->pluck('request_reference')->filter()->unique()->implode(', ') }}</td>
            </tr>
        @endif
        <tr>
            <td class="td_label">ORDER REF.</td>
            <td class="td_value">{{ $return->order_reference ?? '-' }}</td>
        </tr>
        <tr>
            <td class="td_label">ORDER ID</td>
            <td class="td_value">{{ $return->id_order }}</td>
        </tr>
        <tr>
            <td class="td_label">CLIENTE</td>
            <td class="td_value">{{ $return->customer_name ?: '-' }}</td>
        </tr>
        <tr>
            <td class="td_label">ESTADO ATUAL</td>
            <td class="td_value">{{ $return->state_name ?: $return->state }}</td>
        </tr>
    </table>

    @if($details->isNotEmpty())
        <table class="rma-detail-table rma-lines">
            @foreach($details as $detail)
                <tr>
                    <td class="td_label">PRODUTO</td>
                    <td class="td_value">
                        {{ $detail->product_quantity + 0 }} x {{ $detail->product_reference ?: '-' }}
                        @if(!empty($detail->product_name))
                            <br>{{ $detail->product_name }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
