@php
    $variant = $variant ?? 'list';
    $iconPath = $iconPath ?? 'public/assets/admin-module/img/icons/empty-state-document.png';
    $titleKey = $variant === 'search' ? 'No result found' : 'No data found';
    $showButton = $showButton ?? false;
    $shouldShowButton = $showButton && $variant !== 'search';
    $buttonTextKey = $buttonTextKey ?? 'Add new data';
    $buttonUrl = $buttonUrl ?? null;
    $buttonAttributes = $buttonAttributes ?? null;
    $autoColspan = $autoColspan ?? true;
    $resolvedColspan = $colspan ?? 1;
@endphp

<tr class="text-center">
    <td colspan="{{ $resolvedColspan }}"
        class="py-5"
        @if($autoColspan) data-empty-state-auto-colspan="true" @endif>
        <div class="text-center">
            <img src="{{ asset($iconPath) }}" alt="">
            <span class="mt-1 d-block">{{ translate($titleKey) }}</span>

            @if($shouldShowButton)
                <div class="text-center">
                    @if($buttonUrl)
                        <a href="{{ $buttonUrl }}"
                           class="rounded mx-auto btn btn--primary transition text-nowrap fz-12 fw-semibold d-inline-flex align-items-center gap-1 px-3 mt-3">
                            <span class="material-symbols-outlined">add_circle</span>
                            {{ translate($buttonTextKey) }}
                        </a>
                    @elseif($buttonAttributes)
                        <button type="button" {!! $buttonAttributes !!}
                                class="rounded mx-auto btn btn--primary transition text-nowrap fz-12 fw-semibold d-flex align-items-center gap-1 px-3 mt-3">
                            <span class="material-symbols-outlined">add_circle</span>
                            {{ translate($buttonTextKey) }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </td>
</tr>

@once
    @push('script')
        <script>
            "use strict";

            window.syncTableEmptyStateColspan = function (root = document) {
                root.querySelectorAll('[data-empty-state-auto-colspan="true"]').forEach((cell) => {
                    const table = cell.closest('table');
                    const headerRow = table?.querySelector('thead tr:last-child');

                    if (!headerRow) {
                        return;
                    }

                    const colspan = Array.from(headerRow.cells).reduce((total, headerCell) => {
                        return total + (headerCell.colSpan || 1);
                    }, 0);

                    if (colspan > 0) {
                        cell.colSpan = colspan;
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', () => window.syncTableEmptyStateColspan());
        </script>
    @endpush
@endonce
