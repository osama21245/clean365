<?php ($instruction = $instruction ?? null); ?>
<?php ($instructionRatio = $instructionRatio ?? null); ?>
<?php ($formats = $formats ?? implode(', ', array_column(IMAGEEXTENSION, 'key'))); ?>
<?php ($maxFileSize = $maxFileSize ?? readableUploadMaxFileSize('image')); ?>
<?php ($class = $class ?? 'text-muted fs-12 mb-0'); ?>

<?php if($instruction || $instructionRatio): ?>
    <p class="<?php echo e($class); ?>">
        <?php if($instruction): ?>
            <?php echo e($instruction); ?>

        <?php else: ?>
            <span><?php echo e(translate('Image format - :formats', ['formats' => $formats])); ?></span>
            <span class="mx-1">|</span>
            <span><?php echo e(translate('Image Size - maximum size :size', ['size' => $maxFileSize])); ?></span>
            <?php if($instructionRatio): ?>
                <span class="mx-1">|</span>
                <span><?php echo e(translate('Image Ratio - :ratio', ['ratio' => $instructionRatio])); ?></span>
            <?php endif; ?>
        <?php endif; ?>
    </p>
<?php endif; ?>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/admin/partials/_image-upload-instruction.blade.php ENDPATH**/ ?>