<div class="mb-20 nav-tabs-responsive position-relative">
    <ul class="nav nav--tabs scrollbar-w flex-nowrap white-nowrap overflow-x-auto flex-wrap-nowrap nav--tabs__style2">
        <li class="nav-item">
            <a href="<?php echo e(route('admin.configuration.third-party', ['webPage' => 'payment_config', 'type' => 'digital_payment'])); ?>" class="nav-link <?php echo e(request()->has('type') && request()->type == 'digital_payment' ? 'active' : ''); ?>">
                <?php echo e(translate('Digital Payment')); ?>

            </a>
        </li>
    </ul>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/partials/payment-method-inline-menu.blade.php ENDPATH**/ ?>