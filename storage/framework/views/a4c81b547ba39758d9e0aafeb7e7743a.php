<li class="nav-item">
    <a class="nav-link <?php echo e($webPage == 'map-api' ? 'active' : ''); ?>"
       href="<?php echo e(route('admin.configuration.third-party', 'map-api')); ?>">
        <?php echo e(translate('Map Api')); ?>

    </a>
</li>

<li class="nav-item">
    <a class="nav-link <?php echo e($webPage == 'storage_connection' ? 'active' :''); ?>"
       href="<?php echo e(route('admin.configuration.third-party', 'storage_connection')); ?>">
        <?php echo e(translate('Storage Connection')); ?>

    </a>
</li>

<li class="nav-item">
    <a class="nav-link <?php echo e($webPage=='app_settings'?'active':''); ?>"
       href="<?php echo e(route('admin.configuration.third-party', 'app_settings')); ?>">
        <?php echo e(translate('App Settings')); ?>

    </a>
</li>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BusinessSettingsModule/Resources/views/admin/configurations/third-party/partials/index-inline-menu.blade.php ENDPATH**/ ?>