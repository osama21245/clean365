<footer class="footer mt-auto">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 d-flex justify-content-center justify-content-md-start mb-2 mb-md-0">
                <?php echo e((business_config('footer_text','business_information'))->live_values??""); ?> <span
                    class="currentYear ml-3"></span>
            </div>
            <div class="col-md-6 d-flex justify-content-center justify-content-md-end">
                <ul class="list-inline list-separator">
                    <li>
                        <a href="<?php echo e(route('admin.business-settings.get-business-information')); ?>">
                            <?php echo e(translate('business_setup')); ?>

                        </a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.profile_update')); ?>"><?php echo e(translate('profile')); ?></a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('admin.dashboard')); ?>">
                            <span class="material-icons">home</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/AdminModule/Resources/views/layouts/partials/_footer.blade.php ENDPATH**/ ?>