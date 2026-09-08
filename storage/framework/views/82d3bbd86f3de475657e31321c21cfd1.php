<?php $__env->startSection('title', translate('Customized Booking Requests')); ?>
<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module/css/daterangepicker.css')); ?>"/>
    <style>
        .filter-aside__body {
            block-size: auto;
        }

        .filter-aside__header .filter-aside__close-btn {
            width: 1.5rem;
            height: 1.5rem;
            min-width: 1.5rem;
            border-radius: 50%;
            background-color: var(--bs-primary-dark);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 0.625rem;
            opacity: 1;
            transition: background-color .2s ease, box-shadow .2s ease;
        }

        .filter-aside__header .filter-aside__close-btn:hover,
        .filter-aside__header .filter-aside__close-btn:focus {
            background-color: var(--bs-primary);
            opacity: 1;
            box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.16);
        }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title"><?php echo e(translate('Filter Customized Booking')); ?></h3>
            <button type="button" class="btn-close p-1 filter-aside__close-btn"></button>
        </div>
        <form action="<?php echo e(route('admin.booking.post.list')); ?>" method="GET" enctype="multipart/form-data" id="filter-form">
            <input type="hidden" name="type" value="<?php echo e($type); ?>">
            <input type="hidden" name="search" value="<?php echo e($search); ?>">
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2"><?php echo e(translate('Select Categories')); ?></h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_id" id="category_selector__select">
                            <option value=""><?php echo e(translate('Select Category')); ?></option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php echo e(($category->id == ($queryParams['category_id'] ?? '')) ? 'selected' : ''); ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="filter-aside__date_range">
                    <div class="filter-aside__zone_select">
                        <h4 class="mb-2 fw-normal"><?php echo e(translate('Select Date Range')); ?></h4>
                        <div class="mb-30">
                            <select class="date-select theme-input-style w-100" name="select_date" id="select_date">
                                <option value=""><?php echo e(translate('Select date')); ?></option>
                                <option value="today" <?php echo e((isset($queryParams['select_date']) && $queryParams['select_date'] == 'today') ? 'selected' : ''); ?>> <?php echo e(translate('Today')); ?></option>
                                <option value="this_week" <?php echo e((isset($queryParams['select_date']) && $queryParams['select_date'] == 'this_week') ? 'selected' : ''); ?>> <?php echo e(translate('This week')); ?></option>
                                <option value="this_month" <?php echo e((isset($queryParams['select_date']) && $queryParams['select_date'] == 'this_month') ? 'selected' : ''); ?>> <?php echo e(translate('This Month')); ?></option>
                                <option value="custom_range" <?php echo e((isset($queryParams['select_date']) && $queryParams['select_date'] == 'custom_range') ? 'selected' : ''); ?>> <?php echo e(translate('Custom Range')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-30 custom-date-range" style="display: none;">
                        <div class="ff-field" data-ff-field>
                            <label for="filter-start-date" class="ff-field__label">
                                <span class="ff-field__label-text"><?php echo e(translate('Start Date')); ?></span>
                            </label>
                            <div class="ff-field__control">
                                <input type="text"
                                       id="filter-start-date"
                                       name="start_date"
                                       class="form-control ff-field__input"
                                       placeholder="<?php echo e(translate('Start Date')); ?>"
                                       value="<?php echo e($queryParams['start_date'] ?? ''); ?>"
                                       data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            </div>
                            <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                        </div>
                    </div>
                    <div class="fw-normal mb-30 custom-date-range" style="display: none;">
                        <div class="ff-field" data-ff-field>
                            <label for="filter-end-date" class="ff-field__label">
                                <span class="ff-field__label-text"><?php echo e(translate('End Date')); ?></span>
                            </label>
                            <div class="ff-field__control">
                                <input type="text"
                                       id="filter-end-date"
                                       name="end_date"
                                       class="form-control ff-field__input"
                                       placeholder="<?php echo e(translate('End Date')); ?>"
                                       value="<?php echo e($queryParams['end_date'] ?? ''); ?>"
                                       data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            </div>
                            <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize" id="reset-btn" type="button"><?php echo e(translate('Clear all Filter')); ?></button>
                    <button class="btn btn--primary text-capitalize" type="submit"><?php echo e(translate('Filter')); ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3">
                    <h2 class="page-title"><?php echo e(translate('Customized Booking Requests')); ?></h2>
                </div>

                <div
                    class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                    <ul class="nav nav--tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo e($type=='all'?'active':''); ?>"
                               href="<?php echo e(request()->fullUrlWithQuery(['type' => 'all', 'page' => null])); ?>"><?php echo e(translate('All')); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e($type=='new_booking_request'?'active':''); ?>"
                               href="<?php echo e(request()->fullUrlWithQuery(['type' => 'new_booking_request', 'page' => null])); ?>"><?php echo e(translate('No-Bid Request Yet')); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e($type=='placed_offer'?'active':''); ?>"
                               href="<?php echo e(request()->fullUrlWithQuery(['type' => 'placed_offer', 'page' => null])); ?>"><?php echo e(translate('Already Bid Requested')); ?></a>
                        </li>
                    </ul>

                    <div class="d-flex gap-2 fw-medium">
                        <span class="opacity-75"><?php echo e(translate('Total Customized Booking')); ?> : </span>
                        <span class="title-color"><?php echo e($posts->total()); ?></span>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                            <form action="<?php echo e(route('admin.booking.post.list')); ?>" method="GET"
                                  class="search-form search-form_style-two">
                                <input type="hidden" name="type" value="<?php echo e($type); ?>">
                                <?php $__currentLoopData = ['category_id', 'select_date', 'start_date', 'end_date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(isset($queryParams[$field])): ?>
                                        <input type="hidden" name="<?php echo e($field); ?>" value="<?php echo e($queryParams[$field]); ?>">
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                    <input type="search" class="theme-input-style search-form__input"
                                           name="search"
                                           value="<?php echo e($search ?? ''); ?>"
                                           placeholder="<?php echo e(translate('Search by customer info')); ?>">
                                </div>
                                <button type="submit" class="btn btn--primary text-capitalize">
                                    <?php echo e(translate('Search')); ?></button>
                            </form>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('booking_export')): ?>
                                <div class="dropdown">
                                    <button type="button"
                                            class="btn btn--secondary text-capitalize dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <span class="material-icons">file_download</span> <?php echo e(translate('download')); ?>

                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                        <li><a class="dropdown-item"
                                               href="<?php echo e(route('admin.booking.post.export', [
                                                    'type' => $type ?? '',
                                                    'search' => $search ?? '',
                                                    'category_id' => $category_id ?? '',
                                                    'select_date' => $select_date ?? '',
                                                    'start_date' => $start_date ?? '',
                                                    'end_date' => $end_date ?? ''
                                                ])); ?>">
                                                <?php echo e(translate('Excel')); ?>

                                            </a></li>

                                    </ul>
                                </div>
                                <?php endif; ?>
                                <button type="button" class="btn text-capitalize filter-btn border px-3">
                                    <span class="material-icons">filter_list</span> <?php echo e(translate('Filter')); ?>

                                    <span class="count"><?php echo e($filterCounter??0); ?></span>
                                </button>
                            </div>
                        </div>

                        <div class="select-table-wrap">
                            <div
                                class="multiple-select-actions border-bottom border-white gap-3 flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-wrap gap-2 gap-lg-4">
                                    <div class="ms-sm-1">
                                        <input type="checkbox" class="multi-checker">
                                    </div>
                                    <p><span class="checked-count">2</span> <?php echo e(translate('Item Selected')); ?></p>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('booking_delete')): ?>
                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                        <button class="btn btn--danger"
                                                id="multi-remove"><?php echo e(translate('Delete')); ?></button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive position-relative">
                                <?php
                                    $adminBidCustomizeTableColspan = 7;
                                    $hasAdminBidCustomizeFilters = (($type ?? 'all') !== 'all')
                                        || filled($search)
                                        || filled($queryParams['category_id'] ?? null)
                                        || filled($queryParams['select_date'] ?? null)
                                        || filled($queryParams['start_date'] ?? null)
                                        || filled($queryParams['end_date'] ?? null);
                                ?>
                                <?php if($type != 'new_booking_request'): ?>
                                    <?php
                                        $adminBidCustomizeTableColspan++;
                                    ?>
                                <?php endif; ?>
                                <table class="table align-middle multi-select-table multi-select-table-booking">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <?php if($type != 'new_booking_request'): ?>
                                            <th><?php echo e(translate('Booking ID')); ?></th>
                                        <?php endif; ?>
                                        <th><?php echo e(translate('Customer Info')); ?></th>
                                        <th><?php echo e(translate('Booking Request Time')); ?></th>
                                        <th><?php echo e(translate('Service Time')); ?></th>
                                        <th><?php echo e(translate('Category')); ?></th>
                                        <th><?php echo e(translate('Provider Offering')); ?></th>
                                        <th class="text-center"><?php echo e(translate('Action')); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="multi-check" value="<?php echo e($post->id); ?>">
                                            </td>
                                            <?php if($type != 'new_booking_request'): ?>
                                                <?php if($post->booking): ?>
                                                    <td>
                                                        <a href="<?php echo e(route('admin.booking.details', [$post?->booking->id,'web_page'=>'details'])); ?>"><?php echo e($post?->booking->readable_id); ?></a>
                                                    </td>
                                                <?php else: ?>
                                                    <td><small
                                                            class="badge-pill badge-primary"><?php echo e(translate('Not Booked Yet')); ?></small>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <td>
                                                <?php if($post->customer): ?>
                                                    <div>
                                                        <div class="customer-name fw-medium">
                                                            <?php echo e($post->customer?->first_name.' '.$post->customer?->last_name); ?>

                                                        </div>
                                                        <a href="tel:<?php echo e($post->customer?->phone); ?>"
                                                           class="fs-12"><?php echo e($post->customer?->phone); ?></a>
                                                    </div>
                                                <?php else: ?>
                                                    <div><small
                                                            class="disabled"><?php echo e(translate('Customer not available')); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <div><?php echo e($post->created_at->format('Y-m-d')); ?></div>
                                                    <div><?php echo e(format_time_by_business_settings($post->created_at)); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div><?php echo e(date('d-m-Y',strtotime($post->booking_schedule))); ?></div>
                                                    <div><?php echo e(format_time_by_business_settings($post->booking_schedule)); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($post->category): ?>
                                                    <?php echo e($post->category?->name); ?>

                                                <?php else: ?>
                                                    <div><small
                                                            class="disabled"><?php echo e(translate('Category not available')); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <?php ($bids = $post->bids); ?>
                                            <td>
                                                <div class="dropdown-hover">
                                                    <div class="dropdown-hover-toggle"
                                                         data-bs-toggle="dropdown">
                                                        <?php echo e(translate(':count Providers', ['count' => $bids->count() ?? 0])); ?>

                                                    </div>

                                                    <?php if($bids->count() > 0): ?>
                                                        <ul class="dropdown-hover-menu">
                                                            <?php $__currentLoopData = $bids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li>
                                                                    <div class="media gap-3">
                                                                        <div class="avatar border rounded"
                                                                             data-bs-toggle="modal"
                                                                             data-bs-target="#providerInfoModal--<?php echo e($bid->id); ?>">
                                                                            <img class="rounded"
                                                                                 src="<?php echo e(onErrorImage(
                                                                                            $bid->provider?->logo,
                                                                                            asset('storage/app/public/provider/logo').'/' . $bid->provider?->logo,
                                                                                            asset('public/assets/admin-module/img/placeholder.png') ,
                                                                                            'provider/logo/')); ?>"
                                                                                 alt="<?php echo e(translate('logo')); ?>">
                                                                        </div>
                                                                        <div class="media-body">
                                                                            <?php if($bid->provider): ?>
                                                                                <h5 data-bs-toggle="modal"
                                                                                    data-bs-target="#providerInfoModal--<?php echo e($bid->id); ?>"><?php echo e($bid->provider->company_name); ?></h5>
                                                                            <?php else: ?>
                                                                                <small><?php echo e(translate('Provider not available')); ?></small>
                                                                            <?php endif; ?>
                                                                            <div
                                                                                class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                                                                            <span
                                                                                                class="text-danger"><?php echo e(translate('price offered')); ?></span>
                                                                                <h5 class="text-primary"><?php echo e(with_currency_symbol($bid->offered_price)); ?></h5>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <a href="<?php echo e(route('admin.booking.post.details', [$post->id])); ?>"
                                                       type="button"
                                                       class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                       style="--size: 30px">
                                                        <span class="material-icons">visibility</span>
                                                    </a>
    
                                                    <?php if(!$post->booking): ?>
                                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('booking_delete')): ?>
                                                        <a type="button" class="action-btn btn--danger booking-deny"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#exampleModal--<?php echo e($post['id']); ?>"
                                                           style="--size: 30px">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </a>
                                                        <?php endif; ?>
                                                        <div class="modal fade" id="exampleModal--<?php echo e($post['id']); ?>"
                                                             tabindex="-1"
                                                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-body pt-5 p-md-5">
                                                                        <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
    
                                                                        <div class="d-flex justify-content-center mb-4">
                                                                            <img width="75" height="75"
                                                                                 src="<?php echo e(asset('public/assets/admin-module/img/media/delete.png')); ?>"
                                                                                 class="rounded-circle" alt="">
                                                                        </div>
    
                                                                        <h3 class="text-center mb-1 fw-medium"><?php echo e(translate('Are you sure you want to delete the post?')); ?></h3>
                                                                        <p class="text-center fs-12 fw-medium text-muted"><?php echo e(translate('You will lost the custom booking request?')); ?></p>
                                                                        <form method="post"
                                                                              action="<?php echo e(route('admin.booking.post.delete', [$post->id])); ?>">
                                                                            <?php echo csrf_field(); ?>
                                                                            <div class="ff-field ff-field--textarea" data-ff-field>
                                                                                <label for="post-delete-note-<?php echo e($post['id']); ?>" class="ff-field__label">
                                                                                    <span class="ff-field__label-text"><?php echo e(translate('Cancellation Note')); ?><span class="ff-field__required">*</span></span>
                                                                                </label>
                                                                                <div class="ff-field__control">
                                                                                    <textarea name="post_delete_note"
                                                                                              id="post-delete-note-<?php echo e($post['id']); ?>"
                                                                                              class="form-control ff-field__input ff-field__textarea"
                                                                                              placeholder="<?php echo e(translate('Enter the reason for cancellation')); ?>"
                                                                                              rows="3" required></textarea>
                                                                                </div>
                                                                                <div class="ff-field__footer"><div class="ff-field__messages"></div></div>
                                                                            </div>
                                                                            <div
                                                                                class="d-flex justify-content-center gap-3 mt-3">
                                                                                <button type="button"
                                                                                        class="btn btn--secondary"
                                                                                        data-bs-dismiss="modal"><?php echo e(translate('Cancel')); ?></button>
                                                                                <button type="submit"
                                                                                        class="btn btn-danger"><?php echo e(translate('Delete')); ?></button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <?php $__currentLoopData = $bids; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bid): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="modal fade"
                                                 id="providerInfoModal--<?php echo e($bid->id); ?>" tabindex="-1"
                                                 aria-labelledby="providerInfoModalLabel"
                                                 aria-hidden="true">
                                                <div class="modal-dialog modal-lg bs-modal-width">
                                                    <div class="modal-content">
                                                        <div class="modal-header px-sm-4">
                                                            <h4 class="modal-title text-primary"
                                                                id="providerInfoModalLabel"><?php echo e(translate('Provider Information')); ?></h4>
                                                            <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body pb-4 px-lg-4">
                                                            <div
                                                                class="media flex-column flex-sm-row flex-wrap gap-3">
                                                                <img width="173" class="radius-10"
                                                                     src="<?php echo e(onErrorImage(
                                                                            $bid?->provider?->logo,
                                                                            asset('storage/app/public/provider/logo').'/' . $bid?->provider?->logo,
                                                                            asset('public/assets/placeholder.png') ,
                                                                            'provider/logo/')); ?>"
                                                                     alt="<?php echo e(translate('provider-logo')); ?>">
                                                                <div class="media-body">
                                                                    <h5 class="fw-medium mb-1"><?php echo e($bid->provider?->company_name); ?></h5>
                                                                    <div
                                                                        class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                                                            <span
                                                                                class="common-list_rating d-flex gap-1">
                                                                                <span
                                                                                    class="material-icons text-primary fs-12">star</span>
                                                                                <?php echo e($bid->provider?->avg_rating); ?>

                                                                            </span>
                                                                        <span><?php echo e(translate(':count Reviews', ['count' => $bid->provider?->rating_count])); ?></span>
                                                                    </div>

                                                                    <div
                                                                        class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1 mb-3">
                                                                            <span
                                                                                class="text-danger"><?php echo e(translate('Price Offered')); ?></span>
                                                                        <h4 class="text-primary"><?php echo e(with_currency_symbol($bid->offered_price)); ?></h4>
                                                                    </div>
                                                                    <?php if($bid->provider_note): ?>
                                                                        <h3 class="text-muted mb-2"><?php echo e(translate('Description')); ?>

                                                                            :</h3>
                                                                        <p><?php echo e($bid->provider_note); ?></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                            'colspan' => $adminBidCustomizeTableColspan,
                                            'variant' => $hasAdminBidCustomizeFilters ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <?php echo $posts->links(); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script src="<?php echo e(asset('public/assets/admin-module/js/moment.min.js')); ?>"></script>
    <script src="<?php echo e(asset('public/assets/admin-module/js/daterangepicker.min.js')); ?>"></script>
    <script>
        function initSingleDatePicker(selector) {
            const $input = $(selector);

            $input.daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            if ($input.val()) {
                const selectedDate = moment($input.val(), 'YYYY-MM-DD', true);

                if (selectedDate.isValid()) {
                    $input.data('daterangepicker').setStartDate(selectedDate);
                    $input.data('daterangepicker').setEndDate(selectedDate);
                    $input.val(selectedDate.format('YYYY-MM-DD'));
                }
            }

            $input.on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
        }

        $(document).ready(function () {
            initSingleDatePicker('#filter-start-date');
            initSingleDatePicker('#filter-end-date');
        });
    </script>
    <script>
        (function ($) {
            "use strict";

            $('#category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('.category-select').select2({
                placeholder: "<?php echo e(translate('Select Category')); ?>"
            });
            $('.date-select').select2({
                placeholder: "<?php echo e(translate('Select date range')); ?>"
            })

            $('#multi-remove').on('click', function () {
                var request_ids = [];
                $('input:checkbox.multi-check').each(function () {
                    if (this.checked) {
                        request_ids.push($(this).val());
                    }
                });

                Swal.fire({
                    title: "<?php echo e(translate('Are You Sure')); ?>?",
                    text: "<?php echo e(translate('Do you really want to remove the selected requests')); ?>?",
                    type: 'warning',
                    showCloseButton: true,
                    showCancelButton: true,
                    cancelButtonColor: 'var(--bs-secondary)',
                    confirmButtonColor: 'var(--bs-primary)',
                    cancelButtonText: 'Cancel',
                    confirmButtonText: 'Yes',
                    reverseButtons: true

                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: "<?php echo e(route('admin.booking.post.multi-remove')); ?>",
                            data: {
                                post_ids: request_ids,
                            },
                            type: 'post',
                            success: function (response) {
                                toastr.success(response.message)
                                setTimeout(location.reload.bind(location), 1000);
                            },
                            error: function () {

                            }
                        });
                    }
                })

            });

            $('#reset-btn').on('click', function(e) {
                e.preventDefault();
                window.location.href = '<?php echo e(url()->current()); ?>';
            });

            function toggleDateFields() {
                const selectedValue = $('#select_date').val();
                if (selectedValue === 'custom_range') {
                    $('.custom-date-range').show();
                } else {
                    $('.custom-date-range').hide();
                }
            }

            toggleDateFields();

            $('#select_date').change(function() {
                toggleDateFields();
            });

            $('#filter-form').on('submit', function(e) {
                let dateRange = $('#select_date').val();

                if (dateRange === 'custom_range') {
                    const from = $('[name="start_date"]').val();
                    const to = $('[name="end_date"]').val();
                    let hasError = false;

                    if (to === '') {
                        toastr.error('<?php echo e(translate('please select to date')); ?>');
                        hasError = true;
                    }

                    if (from === '') {
                        toastr.error('<?php echo e(translate('please select from date')); ?>');
                        hasError = true;
                    }

                    if (!hasError) {
                        let fromDate = moment(from, 'YYYY-MM-DD', true);
                        let toDate = moment(to, 'YYYY-MM-DD', true);

                        if (!fromDate.isValid() || !toDate.isValid() || fromDate.isAfter(toDate)) {
                            toastr.error('<?php echo e(translate('From date must be earlier than To date')); ?>');
                            hasError = true;
                        }
                    }

                    if (hasError) {
                        e.preventDefault();
                        return;
                    }
                }
            });

        })(jQuery);
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BidModule/Resources/views/admin/customize-list.blade.php ENDPATH**/ ?>