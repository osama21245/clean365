<?php $__env->startSection('title', translate('Booking_List')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/assets/admin-module/css/daterangepicker.css')); ?>"/>
    <style>
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

        .booking-status-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (max-width: 991.98px) {
            .booking-status-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .booking-status-grid {
                grid-template-columns: 1fr;
            }
        }

        .booking-status-card {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1rem 1.125rem;
            border-radius: 0.875rem;
            border: 1px solid transparent;
            text-decoration: none !important;
            background: #fff;
            box-shadow: 0 4px 18px rgba(15, 36, 64, 0.08);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            color: inherit;
            min-height: 92px;
        }

        .booking-status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 36, 64, 0.12);
            color: inherit;
        }

        .booking-status-card.is-active {
            border-color: currentColor;
            box-shadow: 0 8px 28px rgba(15, 36, 64, 0.14);
        }

        .booking-status-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }

        .booking-status-card__icon .material-icons {
            font-size: 1.5rem;
        }

        .booking-status-card__meta {
            min-width: 0;
        }

        .booking-status-card__label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            opacity: 0.75;
            margin-bottom: 0.15rem;
        }

        .booking-status-card__count {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .booking-status-card--accepted {
            color: #1b3a6b;
        }

        .booking-status-card--accepted .booking-status-card__icon {
            background: linear-gradient(135deg, #1b3a6b 0%, #2d5f9a 100%);
        }

        .booking-status-card--accepted.is-active {
            background: linear-gradient(135deg, rgba(27, 58, 107, 0.08) 0%, rgba(45, 95, 154, 0.12) 100%);
        }

        .booking-status-card--ongoing {
            color: #0f7a4a;
        }

        .booking-status-card--ongoing .booking-status-card__icon {
            background: linear-gradient(135deg, #0b6b41 0%, #19a463 100%);
        }

        .booking-status-card--ongoing.is-active {
            background: linear-gradient(135deg, rgba(15, 122, 74, 0.08) 0%, rgba(25, 164, 99, 0.12) 100%);
        }

        .booking-status-card--completed {
            color: #0f766e;
        }

        .booking-status-card--completed .booking-status-card__icon {
            background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        }

        .booking-status-card--completed.is-active {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.08) 0%, rgba(20, 184, 166, 0.12) 100%);
        }

        .booking-status-card--canceled {
            color: #b42318;
        }

        .booking-status-card--canceled .booking-status-card__icon {
            background: linear-gradient(135deg, #b42318 0%, #f04438 100%);
        }

        .booking-status-card--canceled.is-active {
            background: linear-gradient(135deg, rgba(180, 35, 24, 0.08) 0%, rgba(240, 68, 56, 0.12) 100%);
        }

        .booking-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
        }

        .booking-toolbar__search {
            flex: 1 1 280px;
            min-width: 240px;
        }

        .booking-toolbar__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            align-items: center;
        }

        .booking-quick-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
            align-items: center;
        }

        .booking-quick-filters .form-select,
        .booking-quick-filters .form-control {
            min-width: 140px;
            height: 45px;
        }

        .booking-active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .booking-filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(27, 58, 107, 0.08);
            color: #1b3a6b;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .booking-filter-chip a {
            color: inherit;
            line-height: 1;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $currentStatus = $queryParams['booking_status'] ?? 'accepted';
        $statusTabs = [
            'accepted' => [
                'label' => translate('Accepted'),
                'icon' => 'task_alt',
            ],
            'ongoing' => [
                'label' => translate('Ongoing'),
                'icon' => 'autorenew',
            ],
            'completed' => [
                'label' => translate('Completed'),
                'icon' => 'verified',
            ],
            'canceled' => [
                'label' => translate('Canceled'),
                'icon' => 'cancel',
            ],
        ];
        $statusTabQuery = request()->except(['booking_status', 'page']);
        $statusTabQuery['service_type'] = $queryParams['service_type'] ?? 'all';
        $hasActiveFilters = ($filterCounter ?? 0) > 0 || filled($queryParams['search'] ?? null);
    ?>

    <div class="filter-aside">
        <div class="filter-aside__header d-flex justify-content-between align-items-center">
            <h3 class="filter-aside__title"><?php echo e(translate('Filter_your_Booking')); ?></h3>
            <button type="button" class="btn-close p-1 filter-aside__close-btn"></button>
        </div>
        <form action="<?php echo e(route('admin.booking.list')); ?>" method="GET"
            enctype="multipart/form-data" id="filter-form">
            <input type="hidden" name="booking_status" value="<?php echo e($queryParams['booking_status'] ?? 'accepted'); ?>">
            <input type="hidden" name="service_type" value="<?php echo e($queryParams['service_type'] ?? 'all'); ?>">
            <input type="hidden" name="search" value="<?php echo e($queryParams['search'] ?? ''); ?>" id="filter-search">
            <input type="hidden" name="provider_assigned" value="<?php echo e($queryParams['provider_assigned'] ?? ''); ?>">
            <div class="filter-aside__body d-flex flex-column">
                <div class="filter-aside__date_range">
                    <h4 class="fw-normal mb-4"><?php echo e(translate('Select_Date_Range')); ?></h4>
                    <div class="mb-30">
                        <div class="form-floating default-calendar-none">
                            <input type="text" class="form-control" placeholder="<?php echo e(translate('start_date')); ?>"
                                id="start_date" name="start_date" value="<?php echo e($queryParams['start_date']); ?>"
                                data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="start_date"><?php echo e(translate('Start_Date')); ?></label>
                        </div>
                    </div>
                    <div class="fw-normal mb-30">
                        <div class="form-floating default-calendar-none">
                            <input type="text" class="form-control" placeholder="<?php echo e(translate('end_date')); ?>"
                                id="end_date" name="end_date" value="<?php echo e($queryParams['end_date']); ?>"
                                data-ff-datepicker readonly autocomplete="off" inputmode="none">
                            <label for="end_date"><?php echo e(translate('End_Date')); ?></label>
                        </div>
                    </div>
                </div>

                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2"><?php echo e(translate('Select_Categories')); ?></h4>
                    <div class="mb-30">
                        <select class="category-select theme-input-style w-100" name="category_ids[]" multiple="multiple"
                            id="category_selector__select">
                            <option value="all"><?php echo e(translate('Select All')); ?></option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>"
                                    <?php echo e(in_array($category->id, $queryParams['category_ids'] ?? []) ? 'selected' : ''); ?>>
                                    <?php echo e($category->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="filter-aside__category_select">
                    <h4 class="fw-normal mb-2"><?php echo e(translate('Select_Sub_Categories')); ?></h4>
                    <div class="mb-30">
                        <select class="subcategory-select theme-input-style w-100" name="sub_category_ids[]"
                            multiple="multiple" id="sub_category_selector__select">
                            <option value="all"><?php echo e(translate('Select All')); ?></option>
                            <?php $__currentLoopData = $subCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subCategory->id); ?>"
                                    <?php echo e(in_array($subCategory->id, $queryParams['sub_category_ids'] ?? []) ? 'selected' : ''); ?>>
                                    <?php echo e($subCategory->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="filter-aside__zone_select">
                    <h4 class="mb-2 fw-normal"><?php echo e(translate('Select_Zones')); ?></h4>
                    <div class="mb-30">
                        <select class="zone-select theme-input-style w-100" name="zone_ids[]" multiple="multiple"
                            id="zone_selector__select">
                            <option value="all"><?php echo e(translate('Select All')); ?></option>
                            <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($zone->id); ?>"
                                    <?php echo e(in_array($zone->id, $queryParams['zone_ids'] ?? []) ? 'selected' : ''); ?>>
                                    <?php echo e($zone->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-aside__bottom_btns p-20">
                <div class="d-flex justify-content-center gap-20">
                    <button class="btn btn--secondary text-capitalize" id="reset-btn"
                        type="button"><?php echo e(translate('Clear_all_Filter')); ?></button>
                    <button class="btn btn--primary text-capitalize" type="submit"><?php echo e(translate('Filter')); ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div
                        class="page-title-wrap d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 mb-4">
                        <div>
                            <h2 class="page-title mb-1"><?php echo e(translate('Bookings')); ?></h2>
                            <p class="mb-0 opacity-75 fz-12">
                                <?php echo e(translate('Switch status tabs, search, and filter bookings in one place')); ?>

                            </p>
                        </div>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75"><?php echo e(translate('Showing')); ?>:</span>
                            <span class="title-color"><?php echo e($bookings->total()); ?></span>
                        </div>
                    </div>

                    <div class="booking-status-grid mb-4">
                        <?php $__currentLoopData = $statusTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusKey => $statusMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="booking-status-card booking-status-card--<?php echo e($statusKey); ?> <?php echo e($currentStatus === $statusKey ? 'is-active' : ''); ?>"
                               href="<?php echo e(route('admin.booking.list', array_merge($statusTabQuery, ['booking_status' => $statusKey]))); ?>">
                                <span class="booking-status-card__icon">
                                    <span class="material-icons"><?php echo e($statusMeta['icon']); ?></span>
                                </span>
                                <span class="booking-status-card__meta">
                                    <span class="booking-status-card__label"><?php echo e($statusMeta['label']); ?></span>
                                    <span class="booking-status-card__count"><?php echo e($statusCounts[$statusKey] ?? 0); ?></span>
                                </span>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    

                    <div class="card">
                        <div class="card-body">
                            <div class="booking-toolbar mb-3">
                                <form
                                    action="<?php echo e(route('admin.booking.list')); ?>"
                                    class="search-form search-form_style-two booking-toolbar__search" method="GET">
                                    <input type="hidden" name="booking_status" value="<?php echo e($queryParams['booking_status'] ?? 'accepted'); ?>">
                                    <input type="hidden" name="service_type" value="<?php echo e($queryParams['service_type'] ?? 'all'); ?>">
                                    <input type="hidden" name="provider_assigned" value="<?php echo e($queryParams['provider_assigned'] ?? ''); ?>">
                                    <?php $__currentLoopData = ['zone_ids', 'category_ids', 'sub_category_ids', 'start_date', 'end_date']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($queryParams[$field])): ?>
                                            <?php if(is_array($queryParams[$field])): ?>
                                                <?php $__currentLoopData = $queryParams[$field]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <input type="hidden" name="<?php echo e($field); ?>[]" value="<?php echo e($value); ?>">
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <input type="hidden" name="<?php echo e($field); ?>" value="<?php echo e($queryParams[$field]); ?>">
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="<?php echo e($queryParams['search'] ?? ''); ?>" name="search"
                                            placeholder="<?php echo e(translate('Search by booking ID, customer, phone, or provider')); ?>">
                                    </div>
                                    <button type="submit"
                                        class="btn btn--primary"><?php echo e(translate('search')); ?></button>
                                </form>

                                <div class="booking-toolbar__actions">
                                    <div class="booking-quick-filters">
                                        <select class="form-select custom-select" name="provider_assigned" id="providerAssigned">
                                            <option value="" <?php echo e(empty($queryParams['provider_assigned']) || ($queryParams['provider_assigned'] ?? '') == 'all' ? 'selected' : ''); ?>><?php echo e(translate('Provider')); ?>: <?php echo e(translate('All')); ?></option>
                                            <option value="assigned" <?php echo e(($queryParams['provider_assigned'] ?? '') == 'assigned' ? 'selected' : ''); ?>><?php echo e(translate('Assigned')); ?></option>
                                            <option value="unassigned" <?php echo e(($queryParams['provider_assigned'] ?? '') == 'unassigned' ? 'selected' : ''); ?>><?php echo e(translate('Unassigned')); ?></option>
                                        </select>
                                    </div>

                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('booking_export')): ?>
                                        <div class="dropdown">
                                            <button type="button"
                                                class="btn btn--secondary text-capitalize dropdown-toggle h-45"
                                                data-bs-toggle="dropdown">
                                                <span class="material-icons">file_download</span>
                                                <?php echo e(translate('download')); ?>

                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                                <li><a class="dropdown-item"
                                                        href="<?php echo e(route('admin.booking.download', $queryParams)); ?>"><?php echo e(translate('excel')); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <button type="button" class="btn text-capitalize filter-btn border px-3 h-45">
                                        <span class="material-icons">filter_list</span> <?php echo e(translate('Filter')); ?>

                                        <span class="count"><?php echo e($filterCounter ?? 0); ?></span>
                                    </button>
                                </div>
                            </div>

                            <?php if($hasActiveFilters): ?>
                                <div class="booking-active-filters mb-3">
                                    <span class="opacity-75 fz-12"><?php echo e(translate('Active filters')); ?>:</span>
                                    <?php if(filled($queryParams['search'] ?? null)): ?>
                                        <span class="booking-filter-chip">
                                            <?php echo e(translate('Search')); ?>: <?php echo e($queryParams['search']); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if(in_array($queryParams['provider_assigned'] ?? '', ['assigned', 'unassigned'], true)): ?>
                                        <span class="booking-filter-chip">
                                            <?php echo e(translate('Provider')); ?>: <?php echo e(translate(ucfirst($queryParams['provider_assigned']))); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($queryParams['start_date']) || !empty($queryParams['end_date'])): ?>
                                        <span class="booking-filter-chip">
                                            <?php echo e(translate('Date')); ?>: <?php echo e($queryParams['start_date'] ?? '...'); ?> → <?php echo e($queryParams['end_date'] ?? '...'); ?>

                                        </span>
                                    <?php endif; ?>
                                    <?php if(!empty($queryParams['category_ids'])): ?>
                                        <span class="booking-filter-chip"><?php echo e(translate('Categories')); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($queryParams['sub_category_ids'])): ?>
                                        <span class="booking-filter-chip"><?php echo e(translate('Sub Categories')); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($queryParams['zone_ids'])): ?>
                                        <span class="booking-filter-chip"><?php echo e(translate('Zones')); ?></span>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('admin.booking.list', ['booking_status' => $currentStatus, 'service_type' => 'all'])); ?>"
                                       class="btn btn-sm btn--secondary"><?php echo e(translate('Clear all')); ?></a>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle tr-hover">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th><?php echo e(translate('SL')); ?></th>
                                            <th><?php echo e(translate('Booking_ID')); ?></th>
                                            <th><?php echo e(translate('Booking_Date')); ?></th>
                                            <th><?php echo e(translate('Where_Service_will_be_Provided')); ?></th>
                                            <th><?php echo e(translate('Schedule_Date')); ?></th>
                                            <th><?php echo e(translate('Customer_Info')); ?></th>
                                            <th><?php echo e(translate('Provider_Info')); ?></th>
                                            <th><?php echo e(translate('Total_Amount')); ?></th>
                                            <th><?php echo e(translate('Action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                ><?php echo e($key + $bookings?->firstItem()); ?></td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php if($booking->is_repeated): ?>
                                                        <a href="<?php echo e(route('admin.booking.repeat_details', [$booking->id, 'web_page' => 'details'])); ?>">
                                                            <?php echo e($booking->readable_id); ?>

                                                        </a>
                                                        <img width="34" height="34"
                                                             src="<?php echo e(asset('public/assets/admin-module/img/icons/repeat.svg')); ?>"
                                                             class="rounded-circle repeat-icon"
                                                             alt="<?php echo e(translate('repeat')); ?>">
                                                    <?php else: ?>
                                                    <a href="<?php echo e(route('admin.booking.details', [$booking->id, 'web_page' => 'details'])); ?>">
                                                        <?php echo e($booking->readable_id); ?></a>
                                                    <?php endif; ?>
                                                </td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <div><?php echo e(\Carbon\Carbon::parse($booking->created_at)->format('d-M-Y')); ?></div>
                                                    <div><?php echo e(format_time_by_business_settings($booking->created_at)); ?></div>
                                                </td>
                                                <td>
                                                    <?php if($booking->service_location == 'provider'): ?>
                                                        <?php echo e(translate('Provider Location')); ?>

                                                    <?php else: ?>
                                                        <?php echo e(translate('Customer Location')); ?>

                                                    <?php endif; ?>
                                                </td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php if($booking->is_repeated): ?>
                                                        <?php if(empty($booking->nextService)): ?>
                                                            <div><?php echo e(\Carbon\Carbon::parse($booking?->lastRepeat?->service_schedule)->format('d-M-Y')); ?></div>
                                                            <div><?php echo e(format_time_by_business_settings($booking?->lastRepeat?->service_schedule)); ?></div>
                                                        <?php else: ?>
                                                            <span><?php echo e(translate('Next upcoming')); ?></span>
                                                            <div><?php echo e(\Carbon\Carbon::parse($booking?->nextService?->service_schedule)->format('d-M-Y')); ?></div>
                                                            <div><?php echo e(format_time_by_business_settings($booking?->nextService?->service_schedule)); ?></div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div><?php echo e(\Carbon\Carbon::parse($booking->service_schedule)->format('d-M-Y')); ?></div>
                                                        <div><?php echo e(format_time_by_business_settings($booking->service_schedule)); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php ($customer_name = $booking?->service_address?->contact_person_name ?? $booking?->customer?->first_name . ' ' . $booking?->customer?->last_name); ?>
                                                    <?php ($customer_phone = $booking?->service_address?->contact_person_number ?? $booking?->customer?->phone); ?>
                                                    <div>
                                                        <?php if($booking->customer): ?>
                                                            <a
                                                                href="<?php echo e(route('admin.customer.detail', [$booking?->customer?->id, 'web_page' => 'overview'])); ?>">
                                                                <?php echo e(Str::limit($customer_name, 30)); ?>

                                                            </a>
                                                        <?php else: ?>
                                                            <span>
                                                                <?php echo e(Str::limit($customer_name, 30)); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php echo e($customer_phone); ?>

                                                </td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                >
                                                    <?php if(isset($booking->provider)): ?>
                                                        <div>
                                                            <a href="<?php echo e(route('admin.provider.details',[$booking->provider_id, 'web_page'=>'overview'])); ?>"><?php echo e($booking->provider->company_name); ?></a>
                                                        </div>
                                                        <span class="text-light-gray"><?php echo e($booking->provider->company_phone); ?></span>
                                                    <?php else: ?>
                                                        <span class="badge badge badge-danger radius-50">
                                                            <?php echo e(translate('unassigned')); ?>

                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td
                                                    <?php if($booking->is_repeated): ?>
                                                        data-bs-custom-class="review-tooltip custom"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-html="true"
                                                        data-bs-placement="bottom"
                                                        data-bs-title="<?php echo e(translate('This is a repeat booking.')); ?> <br> <?php echo e(translate('Customer has requested total :count bookings under this booking.', ['count' => count($booking->repeat)])); ?> <br> <?php echo e(translate('Check the details')); ?>"
                                                    <?php endif; ?>
                                                ><?php echo e(with_currency_symbol($booking->total_booking_amount)); ?></td>
                                                <td>
                                                    <div class="table-actions d-flex gap-2">
                                                        <?php if($booking->is_repeated): ?>
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                        class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                        style="--size: 30px" data-bs-toggle="dropdown">
                                                                    <span class="material-icons">visibility</span>
                                                                </button>
                                                                <ul
                                                                    class="dropdown-menu border-none dropdown-menu-lg dropdown-menu-right">
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            href="<?php echo e(route('admin.booking.repeat_details', [$booking->id, 'web_page' => 'details'])); ?>">
                                                                                <span
                                                                                    class="material-icons">visibility</span>
                                                                            <?php echo e(translate('Full_Booking_Details')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php if($booking->nextServiceId && $booking['booking_status'] != 'pending'): ?>
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            href="<?php echo e(route('admin.booking.repeat_single_details', [$booking->nextServiceId, 'web_page' => 'details'])); ?>">
                                                                                <span
                                                                                    class="material-icons">visibility</span>
                                                                            <?php echo e(translate('Ongoing_Booking_Details')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                        class="action-btn btn--light-primary fw-medium text-capitalize fz-14"
                                                                        style="--size: 30px" data-bs-toggle="dropdown">
                                                                    <span class="material-icons">download</span>
                                                                </button>
                                                                <ul
                                                                    class="dropdown-menu border-none dropdown-menu-lg dropdown-menu-right">
                                                                    <li class="mx-2"><a
                                                                            class="dropdown-item d-flex align-items-center gap-1"
                                                                            target="_blank"
                                                                            href="<?php echo e(route('admin.booking.full_repeat_invoice', [$booking->id])); ?>">
                                                                                <span
                                                                                    class="material-icons">download</span>
                                                                            <?php echo e(translate('Full invoice')); ?>

                                                                        </a>
                                                                    </li>
                                                                    <?php if($booking->nextServiceId && $booking['booking_status'] != 'pending'): ?>
                                                                        <li class="mx-2">
                                                                            <a
                                                                                class="dropdown-item d-flex align-items-center gap-1"
                                                                                target="_blank"
                                                                                href="<?php echo e(route('admin.booking.single_invoice', [$booking->nextServiceId])); ?>">
                                                                                    <span
                                                                                        class="material-icons">download</span>
                                                                                <?php echo e(translate('Ongoing Booking invoice')); ?>

                                                                            </a>
                                                                        </li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        <?php else: ?>
                                                            <a href="<?php echo e(route('admin.booking.details', [$booking->id, 'web_page' => 'details'])); ?>"
                                                                type="button"
                                                                class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                                style="--size: 30px">
                                                                <span class="material-icons">visibility</span>
                                                            </a>
                                                            <a href="<?php echo e(route('admin.booking.invoice', [$booking->id])); ?>"
                                                                type="button" target="_blank"
                                                                class="action-btn tooltip-hide btn--light-primary fw-medium text-capitalize fz-14"
                                                                style="--size: 30px">
                                                                <span class="material-icons">download</span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <?php echo $__env->make('adminmodule::layouts.partials.components._empty-state', [
                                                'colspan' => 10,
                                                'variant' => $hasActiveFilters ? 'search' : 'list'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                <?php echo $bookings->links(); ?>

                            </div>
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
            initSingleDatePicker('#start_date');
            initSingleDatePicker('#end_date');
        });
    </script>
    <script>
        (function($) {
            "use strict";

            $('#category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('#sub_category_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('#zone_selector__select').on('change', function() {
                var selectedValues = $(this).val();
                if (selectedValues !== null && selectedValues.includes('all')) {
                    $(this).find('option').not(':disabled').prop('selected', 'selected');
                    $(this).find('option[value="all"]').prop('selected', false);
                }
            });

            $('.category-select').select2({
                placeholder: "<?php echo e(translate('Select Category')); ?>"
            });
            $('.subcategory-select').select2({
                placeholder: "<?php echo e(translate('Select Subcategory')); ?>"
            });
            $('.zone-select').select2({
                placeholder: "<?php echo e(translate('Select Zone')); ?>"
            });

            function updateQueryParam(key, value) {
                var url = new URL(window.location.href);
                if (!value || value === 'all') {
                    url.searchParams.delete(key);
                } else {
                    url.searchParams.set(key, value);
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }

            $('#providerAssigned').change(function() {
                updateQueryParam('provider_assigned', $(this).val());
            });
        })(jQuery);
    </script>

    <script>
        $(document).ready(function() {
            $('#reset-btn').on('click', function(e) {
                e.preventDefault();
                window.location.href = '<?php echo e(route('admin.booking.list', ['booking_status' => $currentStatus, 'service_type' => 'all'])); ?>';
            });
            $('#filter-form').on('submit', function(e) {
                const from = $('#start_date').val();
                const to = $('#end_date').val();
                let hasError = false;

                if ((from && !to) || (!from && to)) {
                    toastr.error('<?php echo e(translate('Both start date and end date are required')); ?>');
                    hasError = true;
                }

                if (!hasError && from && to) {
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
            });

        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BookingModule/Resources/views/admin/booking/list.blade.php ENDPATH**/ ?>