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
                $isInfo = in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration', 'non_standard'], true);
                $hasDetails = (int) $counter->counter > 0;
                $hasAction = !$isInfo && $hasDetails;
                $state = $isInfo ? 'info' : ($counter->counter < 1 ? 'ok' : 'alert');
                $stateLabel = $isInfo ? 'Informational' : ($counter->counter < 1 ? 'OK' : 'Needs attention');
            @endphp
        
            <div class="col-xl-3 col-md-6 nt-monitoring-grid__column" data-nt-monitoring-pending="{{ (!$isInfo && $hasAction) ? 'true' : 'false' }}">
                <div class="navbar navbar-light customPanel nt-monitoring-card nt-monitoring-card--{{ $state }}">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                data-panel-title="{{ __("dashboard.$counter->name") }}"
                                @if($hasDetails && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($hasDetails)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                class="nt-monitoring-card__summary {{ $hasDetails ? 'is-actionable' : '' }}"
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

                        @if($hasDetails)
                            <button type="button" class="nt-monitoring-card__footer" @if($headerUrl) onclick="window.open('{{ $headerUrl }}', '_blank')" @else onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')" @endif>
                                <span>View details</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @elseif($isInfo)
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Informational</span><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            </div>
                        @else
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Up to date</span><i class="fa-solid fa-check" aria-hidden="true"></i>
                            </div>
                        @endif

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
                $isInfo = in_array($counter->panel, ['products_pack', 'global_discounts', 'newsletter_registration', 'non_standard'], true);
                $hasDetails = (int) $counter->counter > 0;
                $hasAction = !$isInfo && $hasDetails;
                $state = $isInfo ? 'info' : ($counter->counter < 1 ? 'ok' : 'alert');
                $stateLabel = $isInfo ? 'Informational' : ($counter->counter < 1 ? 'OK' : 'Needs attention');
            @endphp
            <div class="col-xl-3 col-md-6 nt-monitoring-grid__column" data-nt-monitoring-pending="{{ (!$isInfo && $hasAction) ? 'true' : 'false' }}">
                <div class="navbar navbar-light customPanel nt-monitoring-card nt-monitoring-card--{{ $state }}">
                    <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="text-transform:uppercase;">
                            <div
                                id="{{ $panelDomId }}_header"
                                data-panel-title="{{ __("dashboard.$counter->name") }}"
                                @if($hasDetails && $headerUrl)
                                    onclick="window.open('{{ $headerUrl }}', '_blank')"
                                @elseif($hasDetails)
                                    onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')"
                                @endif
                                class="nt-monitoring-card__summary {{ $hasDetails ? 'is-actionable' : '' }}"
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

                        @if($hasDetails)
                            <button type="button" class="nt-monitoring-card__footer" @if($headerUrl) onclick="window.open('{{ $headerUrl }}', '_blank')" @else onclick="getPanelContent('{{ $counter->tab }}', '{{ $counter->panel }}', '{{ $counter->store }}', '{{ $panelDomId }}')" @endif>
                                <span>View details</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        @elseif($isInfo)
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Informational</span><i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            </div>
                        @else
                            <div class="nt-monitoring-card__footer nt-monitoring-card__footer--quiet">
                                <span>Up to date</span><i class="fa-solid fa-check" aria-hidden="true"></i>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @endforeach
    @endif

</div>

<div class="nt-monitoring-empty" hidden>
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    <span>No pending actions.</span>
</div>

<div class="modal fade nt-monitoring-modal" id="ntMonitoringModal" tabindex="-1" role="dialog" aria-labelledby="ntMonitoringModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="nt-monitoring-modal__heading">
                    <span class="nt-monitoring-modal__mark"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                    <div>
                        <span class="nt-monitoring-modal__eyebrow">Monitoring</span>
                        <h5 class="modal-title" id="ntMonitoringModalTitle">Monitoring details</h5>
                        <p class="nt-monitoring-modal__hint">Review the listed items and mark resolved issues when applicable.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ntMonitoringModalBody"></div>
        </div>
    </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('[data-nt-monitoring-toggle]');
        const section = toggle?.closest('.nt-monitoring-section');

        if (!toggle || !section) {
            return;
        }

        const cards = Array.from(section.querySelectorAll('[data-nt-monitoring-pending]'));
        const emptyState = section.querySelector('.nt-monitoring-empty');
        const label = toggle.querySelector('[data-nt-monitoring-toggle-label]');

        const refresh = function () {
            const pendingOnly = toggle.getAttribute('aria-pressed') === 'true';
            let visibleCards = 0;

            cards.forEach(function (card) {
                const visible = !pendingOnly || card.dataset.ntMonitoringPending === 'true';
                card.hidden = !visible;
                visibleCards += visible ? 1 : 0;
            });

            toggle.setAttribute('aria-pressed', pendingOnly ? 'true' : 'false');
            label.textContent = pendingOnly ? 'Pending only' : 'All panels';
            emptyState.hidden = visibleCards !== 0;
        };

        toggle.addEventListener('click', function () {
            toggle.setAttribute('aria-pressed', toggle.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
            refresh();
        });

        refresh();
    });

    function getPanelContent(tab, panel, store, panelDomId)
    {
        const header = document.getElementById(panelDomId + '_header');
        const title = header ? header.dataset.panelTitle : 'Monitoring details';

        $('#ntMonitoringModalTitle').text(title);
        $('#ntMonitoringModalBody').html('<div class="nt-monitoring-modal__loading"><span class="spinner"></span><span>Loading details…</span></div>');

        const modalElement = document.getElementById('ntMonitoringModal');
        if (modalElement && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
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
                    $('#ntMonitoringModalBody').html('<div class="alert alert-danger mb-0">Panel could not be loaded.</div>');
                    return;
                }

                if (response.html.update_tag == 1) {
                    $('#' + panelDomId + '_quantity').text(response.html.counter);
                }

                $('#ntMonitoringModalBody').html(response.html.html);
            },
            error: function() {
                $('#ntMonitoringModalBody').html('<div class="alert alert-danger mb-0">Panel could not be loaded.</div>');
            },
            complete: function() {
                $('#' + panelDomId + '_loading').hide();
                $('#' + panelDomId + '_quantity').show();
            }
        });
    }
</script>
