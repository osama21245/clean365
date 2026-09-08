<?php $__env->startSection('title', translate('Ad Broadcasts')); ?>

<?php $__env->startSection('content'); ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                <h2 class="page-title"><?php echo e(translate('Ad Broadcasts')); ?></h2>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('push_notification_add')): ?>
                    <a href="<?php echo e(route('admin.ad-broadcast.create')); ?>" class="btn btn--primary">
                        <?php echo e(translate('Compose broadcast')); ?>

                    </a>
                <?php endif; ?>
            </div>

            <div class="card mb-30">
                <div class="card-body p-20">
                    <h5 class="mb-3"><?php echo e(translate('AI automatic push')); ?></h5>
                    <?php if($settings->ai_push_last_error): ?>
                        <div class="alert alert-danger py-2"><?php echo e($settings->ai_push_last_error); ?></div>
                    <?php endif; ?>
                    <?php if($settings->ai_push_last_title): ?>
                        <p class="text-muted fz-12 mb-2"><?php echo e(translate('Last sent')); ?>: <?php echo e($settings->ai_push_last_title); ?></p>
                    <?php endif; ?>
                    <?php if($nextAiPushRun): ?>
                        <p class="text-muted fz-12 mb-3"><?php echo e(translate('Next run')); ?>: <?php echo e($nextAiPushRun->toDateTimeString()); ?></p>
                    <?php endif; ?>
                    <form action="<?php echo e(route('admin.ad-broadcast.update-ai-push')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="ai_push_enabled" value="1"
                                           id="ai_push_enabled" <?php echo e($settings->ai_push_enabled ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="ai_push_enabled"><?php echo e(translate('Enabled')); ?></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(translate('Frequency')); ?></label>
                                <select name="ai_push_frequency" class="form-select theme-input-style">
                                    <?php $__currentLoopData = $aiPushFrequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $minutes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php if($settings->ai_push_frequency === $key): echo 'selected'; endif; ?>><?php echo e($key); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(translate('Send times (HH:MM, comma-separated)')); ?></label>
                                <input type="text" name="ai_push_send_times_text" class="form-control theme-input-style"
                                       value="<?php echo e(implode(',', $settings->ai_push_send_times ?? ['09:00'])); ?>"
                                       onchange="this.form.querySelectorAll('[data-send-time]').forEach(e=>e.remove()); this.value.split(',').forEach(t=>{if(t.trim()){const i=document.createElement('input');i.type='hidden';i.name='ai_push_send_times[]';i.dataset.sendTime=1;i.value=t.trim();this.form.appendChild(i);}});">
                                <?php $__currentLoopData = ($settings->ai_push_send_times ?? ['09:00']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="hidden" name="ai_push_send_times[]" data-send-time value="<?php echo e($time); ?>">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><?php echo e(translate('Audiences')); ?></label>
                                <?php $__currentLoopData = ['customers','providers','servicemen','guests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aud): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ai_push_audiences[]"
                                               value="<?php echo e($aud); ?>" id="aud_<?php echo e($aud); ?>"
                                            <?php echo e(in_array($aud, $settings->ai_push_audiences ?? ['customers'], true) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="aud_<?php echo e($aud); ?>"><?php echo e(translate($aud)); ?></label>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><?php echo e(translate('Admin prompt')); ?></label>
                                <textarea name="ai_push_prompt" rows="3" class="form-control theme-input-style"><?php echo e($settings->ai_push_prompt); ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn--primary"><?php echo e(translate('Save AI settings')); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-20">
                    <h5 class="mb-3"><?php echo e(translate('Broadcast history')); ?></h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                            <tr>
                                <th><?php echo e(translate('Title')); ?></th>
                                <th><?php echo e(translate('Audience')); ?></th>
                                <th><?php echo e(translate('Status')); ?></th>
                                <th><?php echo e(translate('Tokens')); ?></th>
                                <th><?php echo e(translate('Topics')); ?></th>
                                <th><?php echo e(translate('Opened')); ?></th>
                                <th><?php echo e(translate('Date')); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $broadcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($item->title); ?></td>
                                    <td><?php echo e($item->audience); ?></td>
                                    <td><?php echo e($item->fcm_status); ?></td>
                                    <td><?php echo e($item->tokens_success); ?>/<?php echo e($item->tokens_targeted); ?></td>
                                    <td><?php echo e($item->topic_dispatches_ok); ?>/<?php echo e($item->topic_dispatches_total); ?></td>
                                    <td><?php echo e($item->opened_count); ?></td>
                                    <td><?php echo e($item->created_at?->format('Y-m-d H:i')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted"><?php echo e(translate('No broadcasts yet')); ?></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($broadcasts->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminmodule::layouts.new-master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/Modules/PromotionManagement/Resources/views/admin/ad-broadcast/index.blade.php ENDPATH**/ ?>