@extends('adminmodule::layouts.new-master')

@section('title', translate('Ad Broadcasts'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                <h2 class="page-title">{{ translate('Ad Broadcasts') }}</h2>
                @can('push_notification_add')
                    <a href="{{ route('admin.ad-broadcast.create') }}" class="btn btn--primary">
                        {{ translate('Compose broadcast') }}
                    </a>
                @endcan
            </div>

            <div class="card mb-30">
                <div class="card-body p-20">
                    <h5 class="mb-3">{{ translate('AI automatic push') }}</h5>
                    @if($settings->ai_push_last_error)
                        <div class="alert alert-danger py-2">{{ $settings->ai_push_last_error }}</div>
                    @endif
                    @if($settings->ai_push_last_title)
                        <p class="text-muted fz-12 mb-2">{{ translate('Last sent') }}: {{ $settings->ai_push_last_title }}</p>
                    @endif
                    @if($nextAiPushRun)
                        <p class="text-muted fz-12 mb-3">{{ translate('Next run') }}: {{ $nextAiPushRun->toDateTimeString() }}</p>
                    @endif
                    <form action="{{ route('admin.ad-broadcast.update-ai-push') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="ai_push_enabled" value="1"
                                           id="ai_push_enabled" {{ $settings->ai_push_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ai_push_enabled">{{ translate('Enabled') }}</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ translate('Frequency') }}</label>
                                <select name="ai_push_frequency" class="form-select theme-input-style">
                                    @foreach($aiPushFrequencies as $key => $minutes)
                                        <option value="{{ $key }}" @selected($settings->ai_push_frequency === $key)>{{ $key }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ translate('Send times (HH:MM, comma-separated)') }}</label>
                                <input type="text" name="ai_push_send_times_text" class="form-control theme-input-style"
                                       value="{{ implode(',', $settings->ai_push_send_times ?? ['09:00']) }}"
                                       onchange="this.form.querySelectorAll('[data-send-time]').forEach(e=>e.remove()); this.value.split(',').forEach(t=>{if(t.trim()){const i=document.createElement('input');i.type='hidden';i.name='ai_push_send_times[]';i.dataset.sendTime=1;i.value=t.trim();this.form.appendChild(i);}});">
                                @foreach(($settings->ai_push_send_times ?? ['09:00']) as $time)
                                    <input type="hidden" name="ai_push_send_times[]" data-send-time value="{{ $time }}">
                                @endforeach
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ translate('Audiences') }}</label>
                                @foreach(['customers','providers','servicemen','guests'] as $aud)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ai_push_audiences[]"
                                               value="{{ $aud }}" id="aud_{{ $aud }}"
                                            {{ in_array($aud, $settings->ai_push_audiences ?? ['customers'], true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="aud_{{ $aud }}">{{ translate($aud) }}</label>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ translate('Admin prompt') }}</label>
                                <textarea name="ai_push_prompt" rows="3" class="form-control theme-input-style">{{ $settings->ai_push_prompt }}</textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn--primary">{{ translate('Save AI settings') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-20">
                    <h5 class="mb-3">{{ translate('Broadcast history') }}</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th>{{ translate('Title') }}</th>
                                <th>{{ translate('Audience') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th>{{ translate('Tokens') }}</th>
                                <th>{{ translate('Topics') }}</th>
                                <th>{{ translate('Opened') }}</th>
                                <th>{{ translate('Date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($broadcasts as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->audience }}</td>
                                    <td>{{ $item->fcm_status }}</td>
                                    <td>{{ $item->tokens_success }}/{{ $item->tokens_targeted }}</td>
                                    <td>{{ $item->topic_dispatches_ok }}/{{ $item->topic_dispatches_total }}</td>
                                    <td>{{ $item->opened_count }}</td>
                                    <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ translate('No broadcasts yet') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $broadcasts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
