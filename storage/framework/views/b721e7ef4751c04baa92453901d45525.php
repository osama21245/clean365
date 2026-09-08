<?php $__env->startSection('title',translate('terms_and_conditions')); ?>

<?php $__env->startSection('content'); ?>
    <?php ($image = getDataSettingsImageFullPath(key: 'terms_and_conditions_image', settingType: 'pages_setup_image', path: 'page-setup/', defaultPath: asset('public/assets/admin-module/img/page-default.png'))); ?>
    <div class="container pt-3">
        <section class="page-header bg__img" data-img="<?php echo e($image); ?>">
            <h3 class="title"><?php echo e(translate('terms_and_conditions')); ?></h3>
        </section>
    </div>
    <section class="privacy-section py-5">
        <div class="container">
            <?php echo bs_data_text($dataSettings,'terms_and_conditions', 1); ?>

        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.landing.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/resources/views/terms-and-conditions.blade.php ENDPATH**/ ?>