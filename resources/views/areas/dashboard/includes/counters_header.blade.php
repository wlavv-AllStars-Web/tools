<div class="row">

    @if(count($asm) > 0)

        @foreach($asm as $counter)
            @php
                $panelDomId = 'dashboard_panel_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $counter->tab . '_' . $counter->store . '_' . $counter->panel);
                $isDeferred = isset($counter->calculated) && $counter->calculated === false;
                $hideCounter = $counter->tab === 'marketing';
                $headerUrl = $counter->panel === 'reviews'
                    ? \App\Services\Prestashop\PrestashopAdminLinkService::dashboardReviewsUrl($counter->store ?? 'ASM')
                    : null;
            @endphp
        
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                @if($counter->counter > 0 && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($counter->counter > 0 || $isDeferred)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                style="
                                    height: 100px;
                                    border-radius: 5px;
                                    padding: 5px 0;
                                    color: white;
                                    @if(in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration']))
                                        background-color: dodgerblue; cursor: pointer;
                                    @elseif($counter->counter < 1)
                                        background-color: #0BDA51; cursor: {{ $isDeferred ? 'pointer' : 'default' }};
                                    @else
                                        background-color: red; cursor: pointer;
                                    @endif
                                "
                            >
                                @if(!$hideCounter)
                                    <div style="font-size: 35px" id="{{ $panelDomId }}_quantity">
                                        {{ $counter->counter }}
                                    </div>
                                @endif

                                <div id="{{ $panelDomId }}_loading" style="display: none;">
                                    <div class="spinner"></div>
                                </div>

                                <div style="font-size: 16px; margin: 0 10px;">
                                    {{ __("dashboard.$counter->name") }}
                                </div>
                            </div>
                        </div>

                        <div id="{{ $panelDomId }}" data-open="0" class="panel-body" style="display: none; overflow-x: scroll;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif


    @if(count($asd) > 0)

        @foreach($asd as $counter)
            @php
                $panelDomId = 'dashboard_panel_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $counter->tab . '_' . $counter->store . '_' . $counter->panel);
                $isDeferred = isset($counter->calculated) && $counter->calculated === false;
                $hideCounter = $counter->tab === 'marketing';
                $headerUrl = $counter->panel === 'reviews'
                    ? \App\Services\Prestashop\PrestashopAdminLinkService::dashboardReviewsUrl($counter->store ?? 'ASD')
                    : null;
            @endphp
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                @if($counter->counter > 0 && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($counter->counter > 0 || $isDeferred)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                style="
                                    height: 100px;
                                    border-radius: 5px;
                                    padding: 5px 0;
                                    color: white;
                                    @if(in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration']))
                                        background-color: dodgerblue; cursor: pointer;
                                    @elseif($counter->counter < 1)
                                        background-color: #0BDA51; cursor: {{ $isDeferred ? 'pointer' : 'default' }};
                                    @else
                                        background-color: red; cursor: pointer;
                                    @endif
                                "
                            >
                                @if(!$hideCounter)
                                    <div style="font-size: 35px" id="{{ $panelDomId }}_quantity">
                                        {{ $counter->counter }}
                                    </div>
                                @endif

                                <div id="{{ $panelDomId }}_loading" style="display: none;">
                                    <div class="spinner"></div>
                                </div>

                                <div style="font-size: 16px; margin: 0 10px;">
                                    {{ __("dashboard.$counter->name") }}
                                </div>
                            </div>
                        </div>

                        <div id="{{ $panelDomId }}" data-open="0" class="panel-body" style="display: none; overflow-x: scroll;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>

<style>
    .spinner {
        width: 56px;
        height: 56px;
        border: 8px solid #ccc;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    function getPanelContent(tab, panel, store, panelDomId)
    {
        let open = $('#' + panelDomId).attr('data-open');

        if (open == 0) {
            $('#' + panelDomId + '_loading').show();
            $('#' + panelDomId + '_quantity').hide();

            $.ajax({
                type: 'POST',
                url: "{{ route('dashboard.getCountersContent') }}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    tab: tab,
                    panel: panel,
                    store: store
                },
                success: function(response) {
                    if (!response.html || !response.html.html) {
                        $('#' + panelDomId).html('<div class="alert alert-danger" style="margin: 0;">Panel could not be loaded.</div>');
                        $('#' + panelDomId).show().attr('data-open', 1);
                        return;
                    }

                    if (response.html.update_tag == 1) {
                        $('#' + panelDomId + '_quantity').text(response.html.counter);
                    }

                    $('#' + panelDomId).replaceWith(response.html.html);
                    $('#' + panelDomId).attr('data-open', 1);
                },
                error: function() {
                    $('#' + panelDomId).html('<div class="alert alert-danger" style="margin: 0;">Panel could not be loaded.</div>');
                    $('#' + panelDomId).show().attr('data-open', 1);
                },
                complete: function() {
                    $('#' + panelDomId + '_loading').hide();
                    $('#' + panelDomId + '_quantity').show();
                }
            });
        } else {
            $('#' + panelDomId).toggle();
            $('#' + panelDomId).attr('data-open', 0);
        }
    }
</script>
