<section class="area-action-panel">
    <div class="area-action-panel__body">
        <h2 class="nt-section-title">Quick actions</h2>
        <div class="area-action-grid">
            @forelse($accessList as $access)
                <a class="area-action-card" href="{{ $access['url'] }}">
                    <i class="fa-solid {{ $access['icon'] }}"></i>
                    <span>{{ $access['name'] }}</span>
                </a>
            @empty
                <div class="nt-monitoring-empty">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    No quick actions available yet.
                </div>
            @endforelse
        </div>
    </div>
</section>
