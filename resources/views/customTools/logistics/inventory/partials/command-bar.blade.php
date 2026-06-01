<div class="navbar navbar-light customPanel inventory-command-bar">
    <div class="inventory-action-tiles">
        @foreach($actions as $action)
            <a class="inventory-action-tile {{ $action['class'] ?? '' }}" href="{{ $action['url'] }}">
                <i class="{{ $action['icon'] }}"></i>
                <span>{{ $action['label'] }}</span>
            </a>
        @endforeach
    </div>

    @if(!empty($dateRoute))
        <form method="GET" action="{{ $dateRoute }}" class="inventory-date-selector">
            <label class="form-label">DATA:</label>
            <div class="inventory-date-row">
                <input type="date" class="form-control" name="date" value="{{ $date }}">
            </div>
            <button class="btn btn-secondary" type="submit">Abrir</button>
        </form>
    @else
        <div class="inventory-date-selector">
            <label class="form-label">DATA:</label>
            <div class="inventory-date-static">{{ $date ?? now()->toDateString() }}</div>
        </div>
    @endif

    <div class="inventory-top-kpis">
        @foreach($counters as $counter)
            <div class="inventory-kpi {{ $counter['class'] ?? '' }}">
                <i class="{{ $counter['icon'] }}"></i>
                <span>{{ $counter['label'] }}</span>
                <strong>{{ $counter['value'] }}</strong>
            </div>
        @endforeach
    </div>
</div>
