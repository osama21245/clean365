<?php ($maxCount = $maxCount ?? 2); ?>
<?php ($showView = $showView ?? true); ?>
<?php ($showDelete = $showDelete ?? true); ?>
<?php ($images = $images ?? []); ?>
<?php ($imageNames = $imageNames ?? []); ?>
<?php ($imageUrls = $imageUrls ?? []); ?>
<?php ($ratio = $ratio ?? 'ratio-2-1'); ?>
<?php ($height = $height ?? 'h-120'); ?>
<?php ($wrapperId = $wrapperId ?? $id . '_wrapper'); ?>
<?php ($validationField = $validationField ?? $id . '_validation'); ?>
<?php ($disableInstruction = $disableInstruction ?? false); ?>

<div class="d-flex flex-column align-items-start gap-3">
    <div class="d-flex flex-column gap-1">
        <label class="text-dark fs-16 mb-1"><?php echo e($title); ?> <?php if($required ?? false): ?> <span class="text-danger">*</span> <?php endif; ?></label>
        <?php if(!empty($subtitle)): ?>
            <p class="text-muted fs-12 mb-0"><?php echo e($subtitle); ?></p>
        <?php endif; ?>
        <?php if(!$disableInstruction): ?>
            <?php echo $__env->make('adminmodule::admin.partials._image-upload-instruction', [
            'instruction' => $instruction ?? null,
            'instructionRatio' => $instructionRatio ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
    </div>

    <div id="<?php echo e($wrapperId); ?>" class="global-multi-image-upload">
        <input type="hidden"
               name="<?php echo e($validationField); ?>"
               value="<?php echo e(count($images) > 0 ? 1 : ''); ?>"
               class="multi-image-validation-proxy"
               data-upload-wrapper="#<?php echo e($wrapperId); ?>">
        <div class="global-multi-image-upload-grid w-100 d-flex flex-wrap gap-3">
        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php ($rawImage = $imageNames[$key] ?? null); ?>
            <?php ($imageName = is_array($rawImage) ? ($rawImage['image'] ?? basename($image)) : ($rawImage ?: basename($image))); ?>
            <div class="global-image-upload global-multi-image-item position-relative overflow-hidden bg-white border-dashed rounded-2 <?php echo e($ratio); ?> <?php echo e($height); ?> d-center has-image"
                 id="<?php echo e($id); ?>-image-<?php echo e($key); ?>"
                 data-existing-image-item="true">
                <img class="global-image-preview global-multi-image-preview" src="<?php echo e($image); ?>" alt="Preview" />
                <div class="overlay-icons">
                    <?php if($showView): ?>
                        <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover view-icon d-flex align-items-center justify-content-center" title="<?php echo e(translate('View')); ?>" data-bs-toggle="modal" data-bs-target="#imageShowingMOdal" data-file-name="<?php echo e($imageName); ?>">
                            <span class="material-icons">visibility</span>
                        </button>
                    <?php endif; ?>
                    <?php if($showDelete): ?>
                        <button type="button" class="action-btn btn--danger remove-existing-image bg-white outline-danger-hover d-flex align-items-center justify-content-center"
                                data-image="<?php echo e($imageName); ?>"
                                data-id="<?php echo e($id); ?>-image-<?php echo e($key); ?>"
                                data-upload-wrapper="#<?php echo e($wrapperId); ?>"
                                title="<?php echo e(translate('Remove')); ?>">
                            <i class="material-symbols-outlined">delete</i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div id="<?php echo e($id); ?>_picker" class="global-multi-image-picker <?php echo e(count($images) >= $maxCount ? 'd-none' : ''); ?>">
                <!-- Spartan picker will inject items here -->
            </div>
        </div>
        <div class="global-multi-image-warning"></div>
    </div>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/admin/partials/_multiple-image-upload.blade.php ENDPATH**/ ?>