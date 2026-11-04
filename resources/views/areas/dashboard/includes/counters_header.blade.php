<div class="row nt-monitoring-grid">

    @if(!empty($leadingPanel))
        {!! $leadingPanel !!}
    @endif

    @if(count($asm) > 0)

        @foreach($asm as $counter)
            @php
                $panelDomId = 'dashboard_panel_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $counter->tab . '_' . $counter->store . '_' . $counter->panel);
                $isDeferred = isset($counter->calculated) && $counter->calculated === false;
                $headerUrl = $counter->panel === 'reviews'
                    ? \App\Services\Prestashop\PrestashopAdminLinkService::dashboardReviewsUrl($counter->store ?? 'ASM')
                    : null;
                $hasAction = ($counter->counter > 0 && $headerUrl) || $counter->counter > 0 || $isDeferred;
                $isInfo = in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration']);
                $state = $isInfo ? 'info' : ($counter->counter < 1 ? 'ok' : 'alert');
                $stateLabel = $isInfo ? 'Check required' : ($counter->counter < 1 ? 'OK' : 'Needs attention');
            @endphp
        
            <div class="col-xl-3 col-md-6 nt-monitoring-grid__column">
                <div class="navbar navbar-light customPanel nt-monitoring-card nt-monitoring-card--{{ $state }}">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                @if($counter->counter > 0 && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($counter->counter > 0 || $isDeferred)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                class="nt-monitoring-card__summary {{ $hasAction ? 'is-actionable' : '' }}"
                            >
                                <div class="nt-monitoring-card__value" id="{{ $panelDomId }}_quantity">
                                    {{ $counter->counter }}
                                </div>

                                <div class="nt-monitoring-card__loading" id="{{ $panelDomId }}_loading" style="display: none;">
                                    <div class="spinner"></div>
                                </div>

                                <div class="nt-monitoring-card__name">
                                    {{ __("dashboard.$counter->name") }}
                                </div>
                                <div class="nt-monitoring-card__status">
                                    <i class="fa-solid {{ $state === 'alert' ? 'fa-circle-exclamation' : ($state === 'info' ? 'fa-circle-info' : 'fa-circle-check') }}" aria-hidden="true"></i>
                                    <span>{{ $stateLabel }}</span>
                                </div>
                            </div>
                        </div>

                        @if($hasAction)
                            <button type="button" class="nt-monitoring-card__footer" @if($counter->counter > 0 && $headerUrl) onclick="window.open('{{ $headerUrl }}', '_blank')" @else onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')" @endif>
                                <span>View details</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @else
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Up to date</span><i class="fa-solid fa-check" aria-hidden="true"></i>
                            </div>
                        @endif

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
                $headerUrl = $counter->panel === 'reviews'
                    ? \App\Services\Prestashop\PrestashopAdminLinkService::dashboardReviewsUrl($counter->store ?? 'ASD')
                    : null;
                $hasAction = ($counter->counter > 0 && $headerUrl) || $counter->counter > 0 || $isDeferred;
                $isInfo = in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration']);
                $state = $isInfo ? 'info' : ($counter->counter < 1 ? 'ok' : 'alert');
                $stateLabel = $isInfo ? 'Check required' : ($counter->counter < 1 ? 'OK' : 'Needs attention');
            @endphp
            <div class="col-xl-3 col-md-6 nt-monitoring-grid__column">
                <div class="navbar navbar-light customPanel nt-monitoring-card nt-monitoring-card--{{ $state }}">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                @if($counter->counter > 0 && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($counter->counter > 0 || $isDeferred)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                class="nt-monitoring-card__summary {{ $hasAction ? 'is-actionable' : '' }}"
                            >
                                <div class="nt-monitoring-card__value" id="{{ $panelDomId }}_quantity">
                                    {{ $counter->counter }}
                                </div>

                                <div class="nt-monitoring-card__loading" id="{{ $panelDomId }}_loading" style="display: none;">
                                    <div class="spinner"></div>
                                </div>

                                <div class="nt-monitoring-card__name">
                                    {{ __("dashboard.$counter->name") }}
                                </div>
                                <div class="nt-monitoring-card__status">
                                    <i class="fa-solid {{ $state === 'alert' ? 'fa-circle-exclamation' : ($state === 'info' ? 'fa-circle-info' : 'fa-circle-check') }}" aria-hidden="true"></i>
                                    <span>{{ $stateLabel }}</span>
                                </div>
                            </div>
                        </div>

                        @if($hasAction)
                            <button type="button" class="nt-monitoring-card__footer" @if($counter->counter > 0 && $headerUrl) onclick="window.open('{{ $headerUrl }}', '_blank')" @else onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')" @endif>
                                <span>View details</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @else
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Up to date</span><i class="fa-solid fa-check" aria-hidden="true"></i>
                            </div>
                        @endif

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
