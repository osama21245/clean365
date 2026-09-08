@extends('adminmodule::layouts.master')

@section('title', translate('Blog Articles'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h2 class="page-title">{{ translate('Blog') }}</h2>
                    @if(($tab ?? 'list') === 'list')
                        <p class="text-muted mb-0 small">
                            {{ translate('Published on website') }}: <strong>{{ $publishedCount ?? 0 }}</strong>
                            · {{ translate('Total in dashboard') }}: <strong>{{ $totalCount ?? 0 }}</strong>
                        </p>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    @can('blog_add')
                        <a href="{{ route('admin.blog.create') }}" class="btn btn--primary">
                            {{ translate('Add Article') }}
                        </a>
                    @endcan
                </div>
            </div>

            <ul class="nav nav-tabs border-0 mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ ($tab ?? 'list') === 'list' ? 'active' : '' }}"
                       href="{{ route('admin.blog.list', ['tab' => 'list', 'search' => $search]) }}">
                        {{ translate('Articles') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($tab ?? '') === 'automation' ? 'active' : '' }}"
                       href="{{ route('admin.blog.list', ['tab' => 'automation']) }}">
                        {{ translate('Blog automation') }}
                    </a>
                </li>
            </ul>

            @if(($tab ?? 'list') === 'automation')
                @php
                    $status = $automationStatus ?? [];
                    $overall = $status['overall'] ?? 'disabled';
                    $overallBadge = match ($overall) {
                        'healthy' => ['bg-success', translate('Healthy')],
                        'warning' => ['bg-warning text-dark', translate('Needs attention')],
                        'error' => ['bg-danger', translate('Error')],
                        'paused' => ['bg-info', translate('Paused')],
                        default => ['bg-secondary', translate('Disabled')],
                    };
                    $checkIcon = fn (string $state) => match ($state) {
                        'ok' => '✓',
                        'warning' => '!',
                        'error' => '✕',
                        default => '–',
                    };
                    $checkClass = fn (string $state) => match ($state) {
                        'ok' => 'text-success',
                        'warning' => 'text-warning',
                        'error' => 'text-danger',
                        default => 'text-muted',
                    };
                @endphp

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">{{ translate('System status') }}</h5>
                                <span class="badge {{ $overallBadge[0] }}">{{ $overallBadge[1] }}</span>
                            </div>
                            <div class="text-md-end">
                                <div class="text-muted small">{{ translate('Schedule') }}: {{ $status['schedule_label'] ?? translate('Every hour') }}</div>
                                @if(!empty($status['next_run_at']))
                                    <div class="fw-semibold">
                                        {{ translate('Next run') }}:
                                        {{ $status['next_run_at']->format('Y-m-d H:i') }}
                                        <span class="text-muted">({{ $status['next_run_at']->diffForHumans() }})</span>
                                    </div>
                                @elseif(!empty($status['next_run_blocked_reason']))
                                    <div class="text-warning fw-semibold">
                                        {{ translate('Next run') }}: {{ $status['next_run_blocked_reason'] }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">{{ translate('Last scheduler tick') }}</div>
                                    @if(!empty($status['last_schedule_at']))
                                        <div class="fw-semibold">{{ $status['last_schedule_at']->format('Y-m-d H:i') }}</div>
                                        <div class="text-muted small">{{ $status['last_schedule_at']->diffForHumans() }}</div>
                                        @if(!empty($status['last_schedule_message']))
                                            <div class="small mt-1">{{ $status['last_schedule_message'] }}</div>
                                        @endif
                                    @else
                                        <div class="text-muted">{{ translate('Not recorded yet') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">{{ translate('Last successful generation') }}</div>
                                    @if(!empty($status['last_success_at']))
                                        <div class="fw-semibold">{{ $status['last_success_at']->format('Y-m-d H:i') }}</div>
                                        <div class="text-muted small">{{ $status['last_success_at']->diffForHumans() }}</div>
                                    @else
                                        <div class="text-muted">{{ translate('None yet') }}</div>
                                    @endif
                                    @if(!empty($status['last_ai_article']))
                                        <div class="small mt-1">
                                            <code>{{ $status['last_ai_article']->slug }}</code>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">{{ translate('Queue') }}</div>
                                    <div>{{ translate('Pending') }}: <strong>{{ $status['pending_jobs'] ?? 0 }}</strong></div>
                                    <div>{{ translate('Failed') }}: <strong class="{{ ($status['failed_jobs'] ?? 0) > 0 ? 'text-danger' : '' }}">{{ $status['failed_jobs'] ?? 0 }}</strong></div>
                                    @if(!empty($status['last_error']['message']))
                                        <div class="text-danger small mt-2">
                                            {{ translate('Last error') }}: {{ \Illuminate\Support\Str::limit($status['last_error']['message'], 120) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 32px"></th>
                                    <th>{{ translate('Check') }}</th>
                                    <th>{{ translate('Detail') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(($status['checks'] ?? []) as $check)
                                    <tr>
                                        <td class="fw-bold {{ $checkClass($check['status']) }}">{{ $checkIcon($check['status']) }}</td>
                                        <td>{{ $check['label'] }}</td>
                                        <td class="text-muted small">{{ $check['detail'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3 text-muted">
                            {{ translate('Usage') }}:
                            {{ $todayCount }}/{{ $settings['ai_blog_daily_limit'] }} {{ translate('today') }},
                            {{ $monthCount }}/{{ $settings['ai_blog_monthly_limit'] }} {{ translate('this month') }}
                        </div>

                        <form method="POST" action="{{ route('admin.blog.automation.update') }}" class="row g-3">
                            @csrf
                            @method('PUT')

                            <div class="col-12">
                                <input type="hidden" name="ai_blog_automation_enabled" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="ai_blog_automation_enabled" name="ai_blog_automation_enabled"
                                           @checked(old('ai_blog_automation_enabled', $settings['ai_blog_automation_enabled']))>
                                    <label class="form-check-label" for="ai_blog_automation_enabled">
                                        {{ translate('Enable AI automatic blog generation') }}
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ai_blog_daily_limit">{{ translate('Daily blog limit') }}</label>
                                <input type="number" min="1" max="100" required class="form-control"
                                       name="ai_blog_daily_limit" id="ai_blog_daily_limit"
                                       value="{{ old('ai_blog_daily_limit', $settings['ai_blog_daily_limit']) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ai_blog_monthly_limit">{{ translate('Monthly blog limit') }}</label>
                                <input type="number" min="1" max="1000" required class="form-control"
                                       name="ai_blog_monthly_limit" id="ai_blog_monthly_limit"
                                       value="{{ old('ai_blog_monthly_limit', $settings['ai_blog_monthly_limit']) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="ai_blog_prompt">{{ translate('Additional AI instructions') }}</label>
                                <textarea class="form-control" rows="5" name="ai_blog_prompt"
                                          id="ai_blog_prompt">{{ old('ai_blog_prompt', $settings['ai_blog_prompt']) }}</textarea>
                                <small class="text-muted">{{ translate('Appended to the base Gemini blog prompt') }}</small>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn--primary">{{ translate('Update automation') }}</button>
                            </div>
                        </form>

                        @can('blog_add')
                            <hr>
                            <form method="POST" action="{{ route('admin.blog.generate-now') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="limit" value="1">
                                <button type="submit" class="btn btn-outline-primary">
                                    {{ translate('Queue generation now') }}
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                {{ translate('Requires queue worker. Schedule runs hourly when automation is enabled.') }}
                                {{ translate('Use "Queue generation now" to test the pipeline immediately.') }}
                            </small>
                        @endcan
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="tab" value="list">
                            <div class="col-md-8">
                                <input type="text" name="search" value="{{ $search }}" class="form-control"
                                       placeholder="{{ translate('Search by title or slug') }}">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn--primary w-100" type="submit">{{ translate('Search') }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>{{ translate('Title') }}</th>
                                    <th>{{ translate('Slug') }}</th>
                                    <th>{{ translate('Image') }}</th>
                                    <th>{{ translate('AI') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th>{{ translate('Published') }}</th>
                                    <th>{{ translate('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($articles as $article)
                                    <tr>
                                        <td>{{ $article->localeField('title', app()->getLocale() === 'ar' ? 'ar' : 'en') }}</td>
                                        <td><code>{{ $article->slug }}</code></td>
                                        <td>
                                            @if($article->featured_image_full_path)
                                                <img src="{{ $article->featured_image_full_path }}" alt="" style="max-height:40px;border-radius:4px">
                                            @else
                                                <span class="badge bg-warning text-dark">{{ translate('Missing') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($article->generated_by_ai)
                                                <span class="badge bg-info">AI</span>
                                            @else
                                                <span class="badge bg-secondary">Manual</span>
                                            @endif
                                        </td>
                                        <td>
                                            @can('blog_manage_status')
                                                <a href="{{ route('admin.blog.status-update', $article->id) }}"
                                                   class="badge {{ $article->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $article->is_active ? translate('Active') : translate('Inactive') }}
                                                </a>
                                            @else
                                                {{ $article->is_active ? translate('Active') : translate('Inactive') }}
                                            @endcan
                                        </td>
                                        <td>{{ optional($article->published_at)->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @can('blog_update')
                                                    <a href="{{ route('admin.blog.edit', $article->id) }}"
                                                       class="btn btn-sm btn-outline-primary">{{ translate('Edit') }}</a>
                                                @endcan
                                                @can('blog_delete')
                                                    <form method="POST" action="{{ route('admin.blog.delete', $article->id) }}"
                                                          onsubmit="return confirm('{{ translate('Are you sure?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">{{ translate('Delete') }}</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">{{ translate('No articles found') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($articles->hasPages())
                        <div class="card-footer">{{ $articles->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
