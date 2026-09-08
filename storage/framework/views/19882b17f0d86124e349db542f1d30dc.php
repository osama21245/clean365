<?php ($showView = $showView ?? true); ?>
<?php ($showEdit = $showEdit ?? true); ?>
<?php ($showDelete = $showDelete ?? true); ?>
<?php ($image = $image ?? null); ?>
<?php ($placeholder = $placeholder ?? asset('assets/admin-module/img/media/photo_camera.svg')); ?>
<?php ($disableInstruction = $disableInstruction ?? false); ?>

<div class="d-flex flex-column align-items-center gap-3">
    <?php if($title || $subtitle): ?>
        <div class="text-center">
            <div class="text-dark fs-16 mb-1"><?php echo e($title); ?> <?php if($required ?? false): ?>
                    <span class="text-danger">*</span>
                <?php endif; ?></div>
            <div class="text-muted fs-12"><?php echo e($subtitle ?? ''); ?></div>
        </div>
    <?php endif; ?>

    <div class="global-image-upload position-relative max-w-100 overflow-hidden bg-white border-dashed rounded-2 mx-auto <?php echo e($ratio ?? 'ratio-1-1'); ?> <?php echo e($height ?? 'h-120'); ?> d-center <?php echo e($image ? 'has-image' : ''); ?>">
        <input type="file" name="<?php echo e($name); ?>" id="<?php echo e($id); ?>" class="global-image-upload-input"
            accept=".<?php echo e(implode(',.', array_column(IMAGEEXTENSION, 'key'))); ?>"
            data-maxFileSize="<?php echo e(readableUploadMaxFileSize('image')); ?>" <?php echo e($required ? 'required' : ''); ?>>

        <div class="global-upload-box <?php echo e($image ? 'global-upload-box-hidden' : ''); ?>">
            <div class="upload-content text-center">
                <span class="material-symbols-outlined placeholder-icon mb-1 text-primary">photo_camera</span>
                <span class="fz-10 d-block"><?php echo e(translate('Add image')); ?></span>
            </div>
        </div>
        <img class="global-image-preview <?php echo e($image ? '' : 'd-none'); ?>" src="<?php echo e($image); ?>" alt="Preview" />

        <div class="overlay-icons <?php echo e($image ? '' : 'd-none'); ?>">
            <?php if($showView): ?>
                <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover view-icon d-flex align-items-center justify-content-center" title="<?php echo e(translate('View')); ?>" data-bs-toggle="modal" data-bs-target="#imageShowingMOdal" data-file-name="<?php echo e($image ? basename($image) : ''); ?>">
                    <span class="material-icons">visibility</span>
                </button>
            <?php endif; ?>
            <?php if($showEdit): ?>
                <button type="button" class="action-btn btn--light-primary bg-white outline-primary-hover edit-icon d-flex align-items-center justify-content-center" title="<?php echo e(translate('Edit')); ?>">
                    <span class="material-icons">edit</span>
                </button>
            <?php endif; ?>
            <?php if($showDelete): ?>
                <button type="button" class="action-btn btn--danger delete_section bg-white outline-danger-hover remove-icon d-flex align-items-center justify-content-center" title="<?php echo e(translate('Remove')); ?>">
                    <i class="material-symbols-outlined">delete</i>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php if(!$disableInstruction): ?>
        <?php echo $__env->make('adminmodule::admin.partials._image-upload-instruction', [
        'instruction' => $instruction ?? null,
        'instructionRatio' => $instructionRatio ?? null,
        'class' => 'text-muted fs-12 mb-0 text-center'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/admin/partials/_single-image-upload.blade.php ENDPATH**/ ?>