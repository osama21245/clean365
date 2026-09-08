<?php $__env->startSection('title', translate('Blog Articles')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-md-6">
                    <h2 class="page-title"><?php echo e(translate('Blog')); ?></h2>
                    <?php if(($tab ?? 'list') === 'list'): ?>
                        <p class="text-muted mb-0 small">
                            <?php echo e(translate('Published on website')); ?>: <strong><?php echo e($publishedCount ?? 0); ?></strong>
                            · <?php echo e(translate('Total in dashboard')); ?>: <strong><?php echo e($totalCount ?? 0); ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_add')): ?>
                        <a href="<?php echo e(route('admin.blog.create')); ?>" class="btn btn--primary">
                            <?php echo e(translate('Add Article')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <ul class="nav nav-tabs border-0 mb-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(($tab ?? 'list') === 'list' ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.blog.list', ['tab' => 'list', 'search' => $search])); ?>">
                        <?php echo e(translate('Articles')); ?>

                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(($tab ?? '') === 'automation' ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.blog.list', ['tab' => 'automation'])); ?>">
                        <?php echo e(translate('Blog automation')); ?>

                    </a>
                </li>
            </ul>

            <?php if(($tab ?? 'list') === 'automation'): ?>
                <?php
                    $status = $automationStatus ?? [];
                    $overall = $status['overall'] ?? 'disabled';
                    $overallBadge = match ($overall) {
                        'healthy' => ['bg-success', translate('Healthy')],
                        'warning' => ['bg-warning text-dark', translate('Needs attention')],
                        'error' => ['bg-danger', translate('Error')],
                        'paused' => ['bg-info', translate('Paused')],
                        default => ['bg-secondary', translate('Disabled')],
                    };
                    $checkIcon = fn (string $state) => match ($state) {
                        'ok' => '✓',
                        'warning' => '!',
                        'error' => '✕',
                        default => '–',
                    };
                    $checkClass = fn (string $state) => match ($state) {
                        'ok' => 'text-success',
                        'warning' => 'text-warning',
                        'error' => 'text-danger',
                        default => 'text-muted',
                    };
                ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="mb-1"><?php echo e(translate('System status')); ?></h5>
                                <span class="badge <?php echo e($overallBadge[0]); ?>"><?php echo e($overallBadge[1]); ?></span>
                            </div>
                            <div class="text-md-end">
                                <div class="text-muted small"><?php echo e(translate('Schedule')); ?>: <?php echo e($status['schedule_label'] ?? translate('Every hour')); ?></div>
                                <?php if(!empty($status['next_run_at'])): ?>
                                    <div class="fw-semibold">
                                        <?php echo e(translate('Next run')); ?>:
                                        <?php echo e($status['next_run_at']->format('Y-m-d H:i')); ?>

                                        <span class="text-muted">(<?php echo e($status['next_run_at']->diffForHumans()); ?>)</span>
                                    </div>
                                <?php elseif(!empty($status['next_run_blocked_reason'])): ?>
                                    <div class="text-warning fw-semibold">
                                        <?php echo e(translate('Next run')); ?>: <?php echo e($status['next_run_blocked_reason']); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small"><?php echo e(translate('Last scheduler tick')); ?></div>
                                    <?php if(!empty($status['last_schedule_at'])): ?>
                                        <div class="fw-semibold"><?php echo e($status['last_schedule_at']->format('Y-m-d H:i')); ?></div>
                                        <div class="text-muted small"><?php echo e($status['last_schedule_at']->diffForHumans()); ?></div>
                                        <?php if(!empty($status['last_schedule_message'])): ?>
                                            <div class="small mt-1"><?php echo e($status['last_schedule_message']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-muted"><?php echo e(translate('Not recorded yet')); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small"><?php echo e(translate('Last successful generation')); ?></div>
                                    <?php if(!empty($status['last_success_at'])): ?>
                                        <div class="fw-semibold"><?php echo e($status['last_success_at']->format('Y-m-d H:i')); ?></div>
                                        <div class="text-muted small"><?php echo e($status['last_success_at']->diffForHumans()); ?></div>
                                    <?php else: ?>
                                        <div class="text-muted"><?php echo e(translate('None yet')); ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($status['last_ai_article'])): ?>
                                        <div class="small mt-1">
                                            <code><?php echo e($status['last_ai_article']->slug); ?></code>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small"><?php echo e(translate('Queue')); ?></div>
                                    <div><?php echo e(translate('Pending')); ?>: <strong><?php echo e($status['pending_jobs'] ?? 0); ?></strong></div>
                                    <div><?php echo e(translate('Failed')); ?>: <strong class="<?php echo e(($status['failed_jobs'] ?? 0) > 0 ? 'text-danger' : ''); ?>"><?php echo e($status['failed_jobs'] ?? 0); ?></strong></div>
                                    <?php if(!empty($status['last_error']['message'])): ?>
                                        <div class="text-danger small mt-2">
                                            <?php echo e(translate('Last error')); ?>: <?php echo e(\Illuminate\Support\Str::limit($status['last_error']['message'], 120)); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th style="width: 32px"></th>
                                    <th><?php echo e(translate('Check')); ?></th>
                                    <th><?php echo e(translate('Detail')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__currentLoopData = ($status['checks'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="fw-bold <?php echo e($checkClass($check['status'])); ?>"><?php echo e($checkIcon($check['status'])); ?></td>
                                        <td><?php echo e($check['label']); ?></td>
                                        <td class="text-muted small"><?php echo e($check['detail']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3 text-muted">
                            <?php echo e(translate('Usage')); ?>:
                            <?php echo e($todayCount); ?>/<?php echo e($settings['ai_blog_daily_limit']); ?> <?php echo e(translate('today')); ?>,
                            <?php echo e($monthCount); ?>/<?php echo e($settings['ai_blog_monthly_limit']); ?> <?php echo e(translate('this month')); ?>

                        </div>

                        <form method="POST" action="<?php echo e(route('admin.blog.automation.update')); ?>" class="row g-3">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>

                            <div class="col-12">
                                <input type="hidden" name="ai_blog_automation_enabled" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1"
                                           id="ai_blog_automation_enabled" name="ai_blog_automation_enabled"
                                           <?php if(old('ai_blog_automation_enabled', $settings['ai_blog_automation_enabled'])): echo 'checked'; endif; ?>>
                                    <label class="form-check-label" for="ai_blog_automation_enabled">
                                        <?php echo e(translate('Enable AI automatic blog generation')); ?>

                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ai_blog_daily_limit"><?php echo e(translate('Daily blog limit')); ?></label>
                                <input type="number" min="1" max="100" required class="form-control"
                                       name="ai_blog_daily_limit" id="ai_blog_daily_limit"
                                       value="<?php echo e(old('ai_blog_daily_limit', $settings['ai_blog_daily_limit'])); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="ai_blog_monthly_limit"><?php echo e(translate('Monthly blog limit')); ?></label>
                                <input type="number" min="1" max="1000" required class="form-control"
                                       name="ai_blog_monthly_limit" id="ai_blog_monthly_limit"
                                       value="<?php echo e(old('ai_blog_monthly_limit', $settings['ai_blog_monthly_limit'])); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="ai_blog_prompt"><?php echo e(translate('Additional AI instructions')); ?></label>
                                <textarea class="form-control" rows="5" name="ai_blog_prompt"
                                          id="ai_blog_prompt"><?php echo e(old('ai_blog_prompt', $settings['ai_blog_prompt'])); ?></textarea>
                                <small class="text-muted"><?php echo e(translate('Appended to the base Gemini blog prompt')); ?></small>
                            </div>

                            <div class="col-12 d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn--primary"><?php echo e(translate('Update automation')); ?></button>
                            </div>
                        </form>

                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_add')): ?>
                            <hr>
                            <form method="POST" action="<?php echo e(route('admin.blog.generate-now')); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="limit" value="1">
                                <button type="submit" class="btn btn-outline-primary">
                                    <?php echo e(translate('Queue generation now')); ?>

                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                <?php echo e(translate('Requires queue worker. Schedule runs hourly when automation is enabled.')); ?>

                                <?php echo e(translate('Use "Queue generation now" to test the pipeline immediately.')); ?>

                            </small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <form method="GET" class="row g-2">
                            <input type="hidden" name="tab" value="list">
                            <div class="col-md-8">
                                <input type="text" name="search" value="<?php echo e($search); ?>" class="form-control"
                                       placeholder="<?php echo e(translate('Search by title or slug')); ?>">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn--primary w-100" type="submit"><?php echo e(translate('Search')); ?></button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th><?php echo e(translate('Title')); ?></th>
                                    <th><?php echo e(translate('Slug')); ?></th>
                                    <th><?php echo e(translate('Image')); ?></th>
                                    <th><?php echo e(translate('AI')); ?></th>
                                    <th><?php echo e(translate('Status')); ?></th>
                                    <th><?php echo e(translate('Published')); ?></th>
                                    <th><?php echo e(translate('Action')); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $articles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($article->localeField('title', app()->getLocale() === 'ar' ? 'ar' : 'en')); ?></td>
                                        <td><code><?php echo e($article->slug); ?></code></td>
                                        <td>
                                            <?php if($article->featured_image_full_path): ?>
                                                <img src="<?php echo e($article->featured_image_full_path); ?>" alt="" style="max-height:40px;border-radius:4px">
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><?php echo e(translate('Missing')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($article->generated_by_ai): ?>
                                                <span class="badge bg-info">AI</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Manual</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_manage_status')): ?>
                                                <a href="<?php echo e(route('admin.blog.status-update', $article->id)); ?>"
                                                   class="badge <?php echo e($article->is_active ? 'bg-success' : 'bg-danger'); ?>">
                                                    <?php echo e($article->is_active ? translate('Active') : translate('Inactive')); ?>

                                                </a>
                                            <?php else: ?>
                                                <?php echo e($article->is_active ? translate('Active') : translate('Inactive')); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(optional($article->published_at)->format('Y-m-d H:i')); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_update')): ?>
                                                    <a href="<?php echo e(route('admin.blog.edit', $article->id)); ?>"
                                                       class="btn btn-sm btn-outline-primary"><?php echo e(translate('Edit')); ?></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('blog_delete')): ?>
                                                    <form method="POST" action="<?php echo e(route('admin.blog.delete', $article->id)); ?>"
                                                          onsubmit="return confirm('<?php echo e(translate('Are you sure?')); ?>')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button class="btn btn-sm btn-outline-danger" type="submit"><?php echo e(translate('Delete')); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4"><?php echo e(translate('No articles found')); ?></td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if($articles->hasPages()): ?>
                        <div class="card-footer"><?php echo e($articles->links()); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminmodule::layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/BlogModule/Resources/views/admin/articles/index.blade.php ENDPATH**/ ?>