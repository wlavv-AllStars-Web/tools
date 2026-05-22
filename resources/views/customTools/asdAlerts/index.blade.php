@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">{{ $showArchived ? 'ASD - Archived alerts' : 'ASD - Alerts' }}</h5>
        <div style="display: flex; gap: 8px;">
            <a class="btn {{ !$showArchived ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('admin.tools.asd_alerts.index') }}">Active</a>
            <a class="btn {{ $showArchived ? 'btn-primary' : 'btn-secondary' }}" href="{{ route('admin.tools.asd_alerts.index', ['archived' => 1]) }}">Archived</a>
            <a class="btn btn-success" href="{{ route('admin.tools.asd_alerts.create') }}">
                <i class="fa-solid fa-plus"></i> New alert
            </a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif

    <form method="GET" style="width: 100%; margin-bottom: 10px;">
        <div class="row">
            <div class="col-lg-5">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search title or message">
            </div>
            <div class="col-lg-2">
                <select name="status" class="form-control">
                    <option value="">All status</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
            </div>
            <div class="col-lg-2">
                <input type="hidden" name="archived" value="{{ $showArchived ? 1 : 0 }}">
            </div>
            <div class="col-lg-3" style="display: flex; gap: 8px;">
                <button class="btn btn-primary" type="submit" style="width: 100%;">Filter</button>
                <a class="btn btn-secondary" href="{{ route('admin.tools.asd_alerts.index') }}" style="width: 100%;">Clear</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered customTable text-center" style="width: 100%;">
        <thead>
            <tr style="text-transform: uppercase;">
                <th>ID</th>
                <th>Title</th>
                <th>Importance</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alerts as $alert)
                <tr>
                    <td>{{ $alert->id }}</td>
                    <td style="text-align: left;">
                        <strong>{{ $alert->title_en ?: $alert->title ?: 'Untitled alert' }}</strong>
                        <div style="color: #777;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $alert->message_en), 120) }}</div>
                    </td>
                    <td>
                        @if((string) $alert->message_type === '3')
                            <span class="badge bg-danger">Critical</span>
                        @elseif((string) $alert->message_type === '2')
                            <span class="badge bg-warning text-dark">Warning</span>
                        @else
                            <span class="badge bg-info text-dark">Informative</span>
                        @endif
                    </td>
                    <td>
                        @if($alert->isActive())
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ optional($alert->creation_date)->format('Y-m-d H:i') ?? '-' }}</td>
                    <td>
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.tools.asd_alerts.edit', $alert) }}">Edit</a>
                            @if(!$showArchived)
                                <form method="POST" action="{{ route('admin.tools.asd_alerts.destroy', $alert) }}" style="margin: 0;" onsubmit="return confirm('Archive this alert?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit">Archive</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No alerts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(method_exists($alerts, 'links'))
        <div style="width: 100%; margin-top: 10px;">{{ $alerts->links() }}</div>
    @endif
</div>
@endsection
