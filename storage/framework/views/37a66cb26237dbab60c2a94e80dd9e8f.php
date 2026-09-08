<?php
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
?>

<tr class="text-center">
    <td colspan="<?php echo e($resolvedColspan); ?>"
        class="py-5"
        <?php if($autoColspan): ?> data-empty-state-auto-colspan="true" <?php endif; ?>>
        <div class="text-center">
            <img src="<?php echo e(asset($iconPath)); ?>" alt="">
            <span class="mt-1 d-block"><?php echo e(translate($titleKey)); ?></span>

            <?php if($shouldShowButton): ?>
                <div class="text-center">
                    <?php if($buttonUrl): ?>
                        <a href="<?php echo e($buttonUrl); ?>"
                           class="rounded mx-auto btn btn--primary transition text-nowrap fz-12 fw-semibold d-inline-flex align-items-center gap-1 px-3 mt-3">
                            <span class="material-symbols-outlined">add_circle</span>
                            <?php echo e(translate($buttonTextKey)); ?>

                        </a>
                    <?php elseif($buttonAttributes): ?>
                        <button type="button" <?php echo $buttonAttributes; ?>

                                class="rounded mx-auto btn btn--primary transition text-nowrap fz-12 fw-semibold d-flex align-items-center gap-1 px-3 mt-3">
                            <span class="material-symbols-outlined">add_circle</span>
                            <?php echo e(translate($buttonTextKey)); ?>

                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </td>
</tr>

<?php if (! $__env->hasRenderedOnce('698c0b28-2031-474a-816b-1a4051f067ee')): $__env->markAsRenderedOnce('698c0b28-2031-474a-816b-1a4051f067ee'); ?>
    <?php $__env->startPush('script'); ?>
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
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/partials/components/_empty-state.blade.php ENDPATH**/ ?>