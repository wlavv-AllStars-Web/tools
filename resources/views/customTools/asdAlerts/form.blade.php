@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <h5 style="margin: 0;">{{ $mode === 'create' ? 'New ASD alert' : 'Edit ASD alert #' . $alert->id }}</h5>
        <a class="btn btn-secondary" href="{{ route('admin.tools.asd_alerts.index') }}">Back</a>
    </div>

    @if(session('success')) <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger" style="width: 100%;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('admin.tools.asd_alerts.store') : route('admin.tools.asd_alerts.update', $alert) }}" style="width: 100%;">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label>Internal title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $alert->title) }}">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Importance</label>
                    <select name="message_type" class="form-control">
                        @foreach($importanceTypes as $value => $label)
                            <option value="{{ $value }}" @selected((string) old('message_type', $alert->message_type ?: '1') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <label>Status</label>
                    <select name="message_status" class="form-control">
                        <option value="1" @selected((string) old('message_status', $alert->message_status) === '1')>Active</option>
                        <option value="0" @selected((string) old('message_status', $alert->message_status) === '0')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row" style="width: 100%; margin-top: 10px;">
            @foreach($languages as $code => $label)
                <div class="col-lg-4" style="margin-bottom: 12px;">
                    <div style="border: 1px solid #dee2e6; padding: 12px; height: 100%; background: #fff;">
                        <h6 style="margin-bottom: 10px;">{{ strtoupper($code) }} - {{ $label }}</h6>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title_{{ $code }}" class="form-control" maxlength="125" value="{{ old('title_' . $code, $alert->{'title_' . $code}) }}">
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message_{{ $code }}" class="form-control" rows="4">{{ old('message_' . $code, $alert->{'message_' . $code}) }}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 15px;">
            @if($mode === 'edit' && (int) $alert->deleted === 0)
                <button class="btn btn-danger" type="submit" form="delete-alert-form" onclick="return confirm('Archive this alert?')">Archive</button>
            @endif
            <button class="btn btn-success" type="submit">Save</button>
        </div>
    </form>

    @if($mode === 'edit' && (int) $alert->deleted === 0)
        <form id="delete-alert-form" method="POST" action="{{ route('admin.tools.asd_alerts.destroy', $alert) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection
