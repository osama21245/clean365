<?php $__env->startSection('title',translate('provider_list')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-30">
                <h2 class="page-title"><?php echo e(translate('Provider_List')); ?></h2>
            </div>

            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="row mb-4 g-4">
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__total_provider">
                                <h2><?php echo e($topCards['total_providers']); ?></h2>
                                <h3><?php echo e(translate('Total_Providers')); ?></h3>
                                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/subscribed-providers.png')); ?>"
                                     class="absolute-img" alt="<?php echo e(translate('providers')); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__newly_joined">
                                <h2><?php echo e($topCards['total_active_providers']); ?></h2>
                                <h3><?php echo e(translate('Active_Providers')); ?></h3>
                                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/newly-joined.png')); ?>"
                                     class="absolute-img" alt="<?php echo e(translate('providers')); ?>">
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6">
                            <div class="statistics-card statistics-card__not_served">
                                <h2><?php echo e($topCards['total_inactive_providers']); ?></h2>
                                <h3><?php echo e(translate('Inactive_Providers')); ?></h3>
                                <img src="<?php echo e(asset('public/assets/admin-module/img/icons/not-served.png')); ?>"
                                     class="absolute-img" alt="<?php echo e(translate('providers')); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                <ul class="nav nav--tabs">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($status=='all'?'active':''); ?>"
                           href="<?php echo e(request()->fullUrlWithQuery(['status' => 'all', 'page' => null])); ?>">
                            <?php echo e(translate('all')); ?>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($status=='active'?'active':''); ?>"
                           href="<?php echo e(request()->fullUrlWithQuery(['status' => 'active', 'page' => null])); ?>">
                            <?php echo e(translate('active')); ?>

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($status=='inactive'?'active':''); ?>"
                           href="<?php echo e(request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null])); ?>">
                            <?php echo e(translate('inactive')); ?>

                        </a>
                    </li>
                </ul>

                <div class="d-flex gap-2 fw-medium">
                    <span class="opacity-75"><?php echo e(translate('Total_Providers:')); ?></span>
                    <span class="title-color"><?php echo e($providers->total()); ?></span>
                </div>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all-tab-pane">
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <h4 class="m-0">Provider List</h4>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <form action="<?php echo e(url()->current()); ?>" class="d-flex align-items-center gap-0 border rounded" method="GET">
                                        <input type="hidden" name="status" value="<?php echo e($status); ?>">
                                        <input type="search" class="theme-input-style border-0 rounded block-size-36" name="search" value="<?php echo e($search); ?>" placeholder="<?php echo e(translate('search by provider name, email, phone')); ?>">
                                        <button type="submit" class="bg-light border-0 px-2 block-size-36 rounded-end d-flex align-items-center justify-content-center">
                                            <span class="material-symbols-outlined fz-20 opacity-75">
                                                search
                                            </span>
                                        </button>
                                    </form>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_export')): ?>
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn rounded btn--secondary text-capitalize dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <span
                                                    class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <a class="dropdown-item"
                                                   href="<?php echo e(route('admin.provider.download')); ?>?search=<?php echo e($search); ?>&status=<?php echo e($status); ?>">
                                                    <?php echo e(translate('excel')); ?>

                                                </a>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <div class="table-responsive">
                                <?php
                                    $providerIndexTableColspan = 5;
                                ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_manage_status')): ?>
                                    <?php
                                        $providerIndexTableColspan += 2;
                                    ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider_delete', 'provider_update'])): ?>
                                    <?php
                                        $providerIndexTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <table id="example" class="table align-middle">
                                    <thead class="align-middle">
                                    <tr>
                                        <th><?php echo e(translate('Sl')); ?></th>
                                        <th><?php echo e(translate('Provider')); ?></th>
                                        <th class="min-w-120"><?php echo e(translate('Contact_Info')); ?></th>
                                        <th class="min-w-120"><?php echo e(translate('Total_Subscribed_Sub_Categories')); ?></th>
                                        <th class="min-w-120"><?php echo e(translate('Total_Booking_Served')); ?></th>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_manage_status')): ?>
                                            <th><?php echo e(translate('Service Availability')); ?></th>
                                            <th><?php echo e(translate('Status')); ?></th>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider_delete', 'provider_update'])): ?>
                                            <th><?php echo e(translate('Action')); ?></th>
                                        <?php endif; ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $ongoingBookings = 0;
                                        $acceptedBookings = 0;
                                    ?>
                                    <?php $__empty_1 = true; $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($key+$providers->firstItem()); ?></td>
                                            <td>
                                                <div class="media align-items-center gap-3 min-w-200">
                                                    <div class="avatar avatar-lg">
                                                        <a href="<?php echo e(route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])); ?>">
                                                            <img class="avatar-img radius-5" src="<?php echo e($provider->logo_full_path); ?>" alt="<?php echo e(translate('provider-logo')); ?>">
                                                        </a>
                                                    </div>
                                                    <div class="media-body">
                                                        <h5 class="mb-1">
                                                            <a href="<?php echo e(route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])); ?>&provider=<?php echo e($provider->id); ?>">
                                                                <?php echo e($provider->company_name); ?>

                                                                <?php if($provider?->is_suspended && business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values): ?>
                                                                    <span
                                                                        class="text-danger fz-12"><?php echo e(('(' . translate('Suspended') . ')')); ?></span>
                                                                <?php endif; ?>

                                                            </a>
                                                        </h5>
                                                        <span
                                                            class="common-list_rating d-flex align-items-center gap-1">
                                                            <span class="material-icons">star</span>
                                                            <?php echo e($provider->avg_rating); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <h5 class="mb-1"><?php echo e(Str::limit($provider->contact_person_name, 30)); ?></h5>
                                                    <a class="fz-12"
                                                       href="mobileto:<?php echo e($provider->contact_person_phone); ?>"><?php echo e($provider->contact_person_phone); ?></a>
                                                    <a class="fz-12"
                                                       href="mobileto:<?php echo e($provider->contact_person_email); ?>"><?php echo e($provider->contact_person_email); ?></a>
                                                </div>
                                            </td>
                                            <td>
                                                <p><?php echo e($provider->subscribed_services_count); ?></p>
                                            </td>
                                            <td><?php echo e($provider->bookings_count); ?></td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_manage_status')): ?>
                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input provider-service-availability-update"
                                                               id="provider-service-availability-<?php echo e($provider->id); ?>"
                                                               data-route="<?php echo e(route('admin.provider.service_availability', [$provider->id])); ?>"
                                                               data-message="<?php echo e(translate('want_to_update_status')); ?>"
                                                               data-provider-id="<?php echo e($provider->id); ?>"
                                                               type="checkbox" <?php echo e($provider->service_availability?'checked':''); ?>>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>


                                                <td>
                                                    <label class="switcher" data-bs-toggle="modal"
                                                           data-bs-target="#deactivateAlertModal">
                                                        <input class="switcher_input provider-status-update"
                                                               id="provider-status-<?php echo e($provider->id); ?>"
                                                               data-route="<?php echo e(route('admin.provider.status_update', [$provider->id])); ?>"
                                                               data-message="<?php echo e(translate('want_to_update_status')); ?>"
                                                               data-provider-id="<?php echo e($provider->id); ?>"
                                                               type="checkbox" <?php echo e($provider?->owner?->is_active == 1 ? 'checked' : ''); ?>>
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </td>
                                            <?php endif; ?>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['provider_delete', 'provider_update'])): ?>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_update')): ?>
                                                            <a href="<?php echo e(route('admin.provider.edit',[$provider->id])); ?>"
                                                               class="action-btn btn--light-primary"
                                                               style="--size: 30px">
                                                                <span class="material-icons">edit</span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php
                                                            $maxBookingAmount = business_config('max_booking_amount', 'booking_setup')->live_values; ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('provider_delete')): ?>
                                                            <button type="button"
                                                                    class="action-btn btn--danger provider-delete"
                                                                    style="--size: 30px"
                                                                    data-provider="delete-<?php echo e($provider->id); ?>"
                                                                    data-ongoing="<?php echo e($provider->bookings->where('booking_status', 'ongoing')->count() ?? 0); ?>"
                                                                    data-payable="<?php echo e((float) ($provider?->owner?->account?->account_payable ?? 0)); ?>"
                                                                    data-receivable="<?php echo e((float) ($provider?->owner?->account?->account_receivable ?? 0)); ?>"
                                                                    data-accepted="<?php echo e($provider->bookings->where('booking_status', 'accepted')
                                                            ->where('provider_id', $provider->id)
                                                            ->count() ?? 0); ?>"
                                                                    data-url="<?php echo e(route('admin.provider.details',[$provider->id, 'web_page'=>'bookings'])); ?>"
                                                                    data-account-url="<?php echo e(route('admin.provider.details',[$provider->id, 'web_page'=>'overview'])); ?>">
                                                                <span class="material-symbols-outlined">delete</span>
                                                            </button>
                                                            <form
                                                                action="<?php echo e(route('admin.provider.delete',[$provider->id])); ?>"
                                                                method="post" id="delete-<?php echo e($provider->id); ?>"
                                                                class="hidden">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $providerIndexTableColspan,
                                            'variant' => (filled($search) || (($status ?? 'all') !== 'all')) ? 'search' : 'list',
                                            'titleKey' => 'No Provider Found'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <?php echo $providers->links(); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="<?php echo e(asset('/public/assets/provider-module/img/profile-delete.png')); ?>" alt="">
                        <h3><?php echo e(translate('Sorry you can’t delete this provider account!')); ?></h3>
                        <p class="fw-medium"><?php echo e(translate('Provider must have to complete the ongoing and accepted bookings.')); ?></p>
                        <a href="#" id="bookingRequestLink">
                            <button type="reset" class="btn btn--primary"><?php echo e(translate('Booking Request')); ?></button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1" aria-labelledby="accountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="<?php echo e(asset('/public/assets/provider-module/img/profile-delete.png')); ?>" alt="">
                        <h3><?php echo e(translate('Sorry you can’t delete this provider account!')); ?></h3>
                        <p class="fw-medium"><?php echo e(translate('Provider payable, withdrawable and other transaction balances must be cleared before deleting this account.')); ?></p>
                        <a href="#" id="providerAccountLink">
                            <button type="reset" class="btn btn--primary"><?php echo e(translate('View Account Overview')); ?></button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script>
        "use strict";

        $('.provider-service-availability-update, .provider-status-update').on('click', function () {
            let $this = $(this);
            let providerId = $this.data('provider-id');
            let initialState = $this.prop('checked');
            let route = $this.data('route');
            let message = $this.data('message');

            route_alert_reload(route, message, true, initialState ? 1 : 0, $this.attr('id'));
        });

        $('.provider-delete').on('click', function () {
            let provider = $(this).data('provider');
            let url = $(this).data('url');
            let accountUrl = $(this).data('account-url');
            let accepted = $(this).data('accepted');
            let ongoing = $(this).data('ongoing');
            let payable = Number($(this).data('payable')) || 0;
            let receivable = Number($(this).data('receivable')) || 0;
            let message = "<?php echo e(translate('want_to_delete_your_account')); ?>";

            if ('<?php echo e(env('APP_ENV') == 'demo'); ?>') {
                toastr.info('This function is disabled for demo mode', {
                    closeButton: true,
                    progressBar: true
                });
            } else {
                if (accepted !== 0 || ongoing !== 0) {
                    $('#exampleModal').data('url', url);
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('exampleModal'));
                    modal.show();
                } else if (payable !== 0 || receivable !== 0) {
                    $('#accountModal').data('url', accountUrl);
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('accountModal'));
                    modal.show();
                } else {
                    form_alert(provider, message);
                }
            }
        });

        $('#exampleModal').on('show.bs.modal', function (event) {
            let url = $(this).data('url');
            $('#bookingRequestLink').attr('href', url);
        });

        $('#accountModal').on('show.bs.modal', function () {
            let url = $(this).data('url');
            $('#providerAccountLink').attr('href', url);
        });

    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/ProviderManagement/Resources/views/admin/provider/index.blade.php ENDPATH**/ ?>