<section class="area-action-panel">
    <div class="area-action-panel__body">
        <div class="area-action-grid">
            @foreach($accessList as $access)
                <a class="area-action-card" href="{{ $access['url'] }}">
                    <i class="fa-solid {{ $access['icon'] }}"></i>
                    <span>{{ $access['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
