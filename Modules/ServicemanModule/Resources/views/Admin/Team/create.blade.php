@extends('adminmodule::layouts.master')

@section('title', translate('Add_New_Team'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{ translate('Add_New_Team') }}</h2>
            </div>

            <form action="{{ route('admin.team.store') }}" method="post" data-ff-validate novalidate>
                @csrf
                <div class="card mb-30">
                    <div class="card-body p-30">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ translate('Supervisor') }} <span class="text-danger">*</span></label>
                                    <select name="provider_id" id="provider_id" class="theme-input-style js-select" required>
                                        <option value="">{{ translate('Select_Supervisor') }}</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" {{ old('provider_id', $providerId) == $provider->id ? 'selected' : '' }}>
                                                {{ $provider->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                @include('partials._form-field', [
                                    'type' => 'text',
                                    'name' => 'name',
                                    'label' => translate('Team_Name'),
                                    'placeholder' => translate('Enter_Team_Name'),
                                    'icon' => 'groups',
                                    'required' => true,
                                    'value' => old('name')])
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ translate('Status') }}</label>
                                    <select name="is_active" class="theme-input-style js-select">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="form-label">{{ translate('Select_Servicemen') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{ translate('Unassigned servicemen and servicemen already on this supervisor can be added') }}</small>
                                    <select name="serviceman_ids[]" id="serviceman_ids" class="theme-input-style js-select" multiple required>
                                        @foreach($servicemen as $serviceman)
                                            <option value="{{ $serviceman->id }}"
                                                {{ in_array($serviceman->id, old('serviceman_ids', [])) ? 'selected' : '' }}>
                                                {{ $serviceman->user?->first_name }} {{ $serviceman->user?->last_name }}
                                                ({{ $serviceman->user?->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('admin.team.list') }}" class="btn btn--secondary">{{ translate('Cancel') }}</a>
                    <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            $('.js-select').select2({ width: '100%' });

            $('#provider_id').on('change', function () {
                const providerId = $(this).val();
                const $servicemen = $('#serviceman_ids');
                $servicemen.empty();

                if (!providerId) {
                    return;
                }

                $.get("{{ route('admin.team.servicemen-by-provider') }}", { provider_id: providerId }, function (response) {
                    const items = response?.content || response?.data || [];
                    items.forEach(function (item) {
                        $servicemen.append(new Option(item.name + (item.phone ? ' (' + item.phone + ')' : ''), item.id, false, false));
                    });
                    $servicemen.trigger('change');
                });
            });
        });
    </script>
@endpush
