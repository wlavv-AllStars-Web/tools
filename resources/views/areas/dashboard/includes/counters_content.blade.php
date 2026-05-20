<div id="{{ $panelDomId ?? $panel->panel }}" data-open="1" class="panel-body" style="overflow-x: scroll;">
    @php
        $panelDescription = trim((string) ($panel->description ?? ''));
    @endphp

    @if($panelDescription !== '')
        <details class="dashboard-panel-description">
            <summary class="dashboard-panel-description-button">
                Description
            </summary>
            <div class="dashboard-panel-description-content">
                {{ $panelDescription }}
            </div>
        </details>
    @endif

    @if($details->name == 'ASD missing images')
        <div style="text-align: center; margin: 5px;">
            <a class="btn btn-info" style="margin: 5px; margin: 0 auto;" href="{{ route('marketing.asdMissingImages') }}">
                UPDATE ASD MISSING IMAGES
            </a>
        </div>
    @endif

    @if(isset($details->columns))
        <table class="table table-bordered customTable text-center">
            <tr style="text-transform: uppercase">
                @foreach($details->columns as $column)
                    @if($column != 'other' && $column != 'url')
                        @if($column == 'email')
                        @else
                            <td>
                                @if($column == 'clean')
                                @else
                                    {{ __('tags.' . $column) }}
                                @endif
                            </td>
                        @endif
                    @endif
                @endforeach
            </tr>

            @foreach($details->data as $item)
                @php
                    $hasExceptionFields = isset($details->exception_fields)
                        && is_array($details->exception_fields)
                        && count($details->exception_fields) >= 4;

                    $rowExceptionId = null;

                    if ($hasExceptionFields && isset($item[$details->exception_fields[1]])) {
                        $rowExceptionId = $item[$details->exception_fields[1]];
                    }

                    /*
                     * Colunas que nunca devem abrir links BO.
                     * Estas colunas têm ação própria ou são auxiliares.
                     */
                    $nonClickableColumns = [
                        'clean',
                        'delete',
                        'remove',
                        'send_email',
                        'send_email_reviewed',
                        'extra_action',
                        'email',
                        'other',
                        'url',
                    ];

                    $rowUrl = $item['url'] ?? null;

                    if (!$rowUrl && isset($details->prestashop) && is_array($details->prestashop)) {
                        $prestashop = $details->prestashop;

                        if (($prestashop['mode'] ?? null) === 'url') {
                            $field = $prestashop['field'] ?? null;
                            $entity = $prestashop['entity'] ?? null;
                            $store = $prestashop['store'] ?? ($panel->store ?? 'ASM');

                            if ($field && $entity && isset($item[$field])) {
                                $rowUrl = \App\Services\Prestashop\PrestashopAdminLinkService::dashboardUrl(
                                    (string) $entity,
                                    (int) $item[$field],
                                    (string) $store
                                );
                            }
                        }
                    }
                @endphp

                <tr
                    @if($rowExceptionId !== null) id="row_exception_{{ $rowExceptionId }}" @endif
                    @if(isset($item['tr_color'])) class="{{ $item['tr_color'] }}" @endif
                >
                    @foreach($details->columns as $column)
                        @if($column != 'other' && $column != 'url')

                            @if($column == 'extra_action')
                                <td @if(isset($details->link)) onclick="window.location.replace('{{ $details->link }}');" style="cursor: pointer;" @endif>
                                    {!! $item[$column] ?? '' !!}
                                </td>

                            @elseif($column == 'clean')
                                <td class="{{ $column }}">
                                    @if($hasExceptionFields)
                                        <img
                                            src="/admin/images/check.png"
                                            onclick="setRowAsChecked(
                                                '{{ $details->exception_fields[0] }}',
                                                '{{ $item[$details->exception_fields[1]] ?? '' }}',
                                                '{{ $item[$details->exception_fields[2]] ?? '' }}',
                                                '{{ addslashes($item[$details->exception_fields[3]] ?? '') }}',
                                                '{{ $panelDomId ?? $panel->panel }}',
                                                this
                                            )"
                                            style="cursor: pointer;"
                                        >
                                    @endif
                                </td>

                            @elseif($column == 'delete' || $column == 'remove')
                                <td id="delete_{{ $item[$column] ?? ($item['delete'] ?? '') }}">
                                    <i class="fa-solid fa-trash"
                                       onclick="deleteItem({{ $item[$column] ?? ($item['delete'] ?? 0) }}, '{{ $details->table ?? '' }}')"
                                       style="color: red; font-size: 20px; cursor: pointer;"></i>
                                </td>

                            @elseif($column == 'send_email')
                                <td id="send_email_{{ $item['send_email'] ?? '' }}">
                                    <i class="fa-solid fa-envelope"
                                       onclick="requestedProductSendEmail('{{ $item['send_email'] ?? '' }}')"
                                       style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i>
                                </td>

                            @elseif($column == 'send_email_reviewed')
                                <td id="send_email_reviwed_{{ $item['email'] ?? '' }}">
                                    <i class="fa-solid fa-envelope"
                                       onclick="requestedProductSendEmailReviewed({{ $item['id_order'] ?? 0 }}, '{{ $item['email'] ?? '' }}')"
                                       style="color: dodgerblue; font-size: 20px; cursor: pointer;"></i>
                                </td>

                            @else
                                @if($column == 'email')
                                @else
                                    <td
                                        class="{{ $column }}"
                                        @if($rowUrl && !in_array($column, $nonClickableColumns, true))
                                            onclick="window.open('{{ $rowUrl }}', '_blank');"
                                            style="cursor: pointer;"
                                        @elseif(isset($details->link))
                                            onclick="window.location.replace('{{ $details->link }}');"
                                            style="cursor: pointer;"
                                        @endif
                                    >
                                        @if(
                                            ($column == 'pack_quantity') &&
                                            isset($item['pack_quantity'], $item['stock'], $item['cache_is_pack']) &&
                                            ($item['pack_quantity'] > $item['stock']) &&
                                            ($item['cache_is_pack'] == 1)
                                        )
                                            <span style="color: red;">{!! $item[$column] ?? '' !!}</span>
                                        @else
                                            {!! $item[$column] ?? '' !!}
                                        @endif
                                    </td>
                                @endif
                            @endif
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </table>
    @endif
</div>

<style>
    details.dashboard-panel-description > summary::-webkit-details-marker {
        display: none;
    }

    details.dashboard-panel-description > summary::marker {
        content: "";
    }

.dashboard-panel-description {
    width: 100%;
    margin: 0 0 5px;
    padding-top: 5px;
    text-align: center;
}

    .dashboard-panel-description-button {
        display: block;
        width: 100%;
        padding: 9px 14px;
        border: 1px solid #c9d3dc;
        border-radius: 4px;
        background: #f8fafc;
        color: #2f3a45;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        list-style: none;
        user-select: none;
    }

    .dashboard-panel-description-button:hover {
        background: #eef4f8;
        border-color: #9fb0bf;
    }

    .dashboard-panel-description[open] .dashboard-panel-description-button {
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        background: #eef4f8;
    }

    .dashboard-panel-description-content {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #c9d3dc;
        border-top: 0;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        background: #ffffff;
        color: #333;
        text-align: center;
    }

    tr.row_red > td {
        background-color: red;
        color: #FFF;
    }

    tr.row_yellow > td {
        background-color: yellow;
    }
</style>
