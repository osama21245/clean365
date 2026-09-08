<?php
    $type           = $type           ?? 'text';
    $name           = $name           ?? '';
    $cleanName      = preg_replace('/\[\]$/', '', $name);
    $id             = $id             ?? $cleanName;
    $label          = $label          ?? '';
    $labelId        = $labelId        ?? null;
    $value          = $value          ?? old($cleanName);
    $placeholder    = $placeholder    ?? $label;
    $icon           = $icon           ?? null;
    $badge          = $badge          ?? null;
    $badgeVariant   = $badgeVariant   ?? 'primary';
    $required       = $required       ?? false;
    $disabled       = $disabled       ?? false;
    $readonly       = $readonly       ?? false;
    $maxlength      = $maxlength      ?? null;
    $minlength      = $minlength      ?? null;
    $min            = $min            ?? null;
    $max            = $max            ?? null;
    $step           = $step           ?? null;
    $rows           = $rows           ?? 4;
    $charCount      = $charCount      ?? false;
    $hint           = $hint           ?? null;
    $prefix         = $prefix         ?? null;
    $suffix         = $suffix         ?? null;
    $inputClass     = $inputClass     ?? '';
    $wrapClass      = $wrapClass      ?? '';
    $selectClass    = $selectClass    ?? '';
    $options        = $options        ?? [];
    $optionNull     = $optionNull     ?? null;
    $prependOptions = $prependOptions ?? [];
    $multiple       = $multiple       ?? false;
    $radioLayout    = $radioLayout    ?? 'inline';
    $radioItemClass = $radioItemClass ?? 'custom-radio';
    $checked        = $checked        ?? null;
    $autocomplete   = $autocomplete   ?? null;
    $extraAttrs     = $extraAttrs     ?? '';

    $wrapperClasses = trim(
        'ff-field ff-field--' . $type
        . ($icon ? ' ff-field--has-icon' : '')
        . ($prefix ? ' ff-field--has-prefix' : '')
        . ($suffix ? ' ff-field--has-suffix' : '')
        . ($multiple ? ' ff-field--multiple' : '')
        . ' ' . $wrapClass
    );
    $counterId = $id . '_char_count';

    $ffNormalise = function (array $raw) use ($id) {
        $out = [];
        foreach ($raw as $k => $v) {
            if (is_array($v)) {
                $optValue = $v['value'] ?? $k;
                $out[] = [
                    'value'    => $optValue,
                    'label'    => $v['label']    ?? '',
                    'id'       => $v['id']       ?? ($id . '_' . $optValue),
                    'disabled' => $v['disabled'] ?? false,
                    'attrs'    => $v['attrs']    ?? ''];
            } else {
                $out[] = [
                    'value'    => $k,
                    'label'    => $v,
                    'id'       => $id . '_' . $k,
                    'disabled' => false,
                    'attrs'    => ''];
            }
        }
        return $out;
    };

    $normalizedOptions = $ffNormalise($options);
    $normalizedPrepend = $ffNormalise($prependOptions);

    $ffIsSelected = function ($optValue) use ($value, $multiple) {
        if ($multiple) {
            return is_array($value) && in_array((string)$optValue, array_map('strval', $value), true);
        }
        if (is_array($value)) {
            return in_array((string)$optValue, array_map('strval', $value), true);
        }
        return (string)$value === (string)$optValue;
    };

    if (is_array($value) && !$multiple && !in_array($type, ['select', 'radio', 'checkbox', 'switch'])) {
        $value = '';
    }

    $showCounter  = $charCount && $maxlength && in_array($type, ['text', 'textarea']);
?>

<div class="<?php echo e($wrapperClasses); ?>" data-ff-field>
    <?php if($label && !in_array($type, ['checkbox', 'switch'])): ?>
        <label <?php if($labelId): ?> id="<?php echo e($labelId); ?>" <?php endif; ?>
               <?php if(!in_array($type, ['radio'])): ?> for="<?php echo e($id); ?>" <?php endif; ?>
               class="ff-field__label">
            <span class="ff-field__label-text"><?php echo e($label); ?><?php if($required): ?><span class="ff-field__required">*</span><?php endif; ?></span>
            <?php if($badge): ?>
                <span class="ff-field__badge ff-field__badge--<?php echo e($badgeVariant); ?>"><?php echo e($badge); ?></span>
            <?php endif; ?>
        </label>
    <?php endif; ?>

    <div class="ff-field__control">
        <?php if($icon && in_array($type, ['text','email','password','number','tel','url','search','date','datetime-local','time'])): ?>
            <span class="material-icons ff-field__icon"><?php echo e($icon); ?></span>
        <?php endif; ?>

        <?php if($prefix && !in_array($type, ['textarea','select','radio','checkbox','switch'])): ?>
            <span class="ff-field__affix ff-field__prefix"><?php echo e($prefix); ?></span>
        <?php endif; ?>

        <?php switch($type):
            case ('textarea'): ?>
                <textarea
                    name="<?php echo e($name); ?>"
                    id="<?php echo e($id); ?>"
                    class="form-control ff-field__input ff-field__textarea <?php echo e($inputClass); ?>"
                    placeholder="<?php echo e($placeholder); ?>"
                    rows="<?php echo e($rows); ?>"
                    <?php if($maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
                    <?php if($minlength): ?> minlength="<?php echo e($minlength); ?>" <?php endif; ?>
                    <?php if($required): ?> required <?php endif; ?>
                    <?php if($disabled): ?> disabled <?php endif; ?>
                    <?php if($readonly): ?> readonly <?php endif; ?>
                    <?php if($charCount): ?> data-char-count data-char-count-target="#<?php echo e($counterId); ?>" <?php endif; ?>
                    <?php echo $extraAttrs; ?>><?php echo e($value); ?></textarea>
                <?php break; ?>

            <?php case ('select'): ?>
                <select
                    name="<?php echo e($name); ?>"
                    id="<?php echo e($id); ?>"
                    class="form-control ff-field__input ff-field__select <?php echo e($selectClass); ?> <?php echo e($inputClass); ?>"
                    <?php if($multiple): ?> multiple <?php endif; ?>
                    <?php if($required): ?> required <?php endif; ?>
                    <?php if($disabled): ?> disabled <?php endif; ?>
                    <?php echo $extraAttrs; ?>>
                    <?php if($optionNull !== null): ?>
                        <option value="" <?php echo e($value === null || $value === '' ? 'selected' : ''); ?> disabled><?php echo e($optionNull); ?></option>
                    <?php endif; ?>
                    <?php $__currentLoopData = $normalizedPrepend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($opt['value']); ?>"
                            <?php echo e($ffIsSelected($opt['value']) ? 'selected' : ''); ?>

                            <?php if($opt['disabled']): ?> disabled <?php endif; ?>
                            <?php echo $opt['attrs']; ?>><?php echo e($opt['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $normalizedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($opt['value']); ?>"
                            <?php echo e($ffIsSelected($opt['value']) ? 'selected' : ''); ?>

                            <?php if($opt['disabled']): ?> disabled <?php endif; ?>
                            <?php echo $opt['attrs']; ?>><?php echo e($opt['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php break; ?>

            <?php case ('radio'): ?>
                <div class="ff-field__radio-group ff-field__radio-group--<?php echo e($radioLayout); ?>">
                    <?php $__currentLoopData = $normalizedOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="<?php echo e($radioItemClass); ?> ff-field__radio-item">
                            <input type="radio"
                                   id="<?php echo e($opt['id']); ?>"
                                   name="<?php echo e($name); ?>"
                                   value="<?php echo e($opt['value']); ?>"
                                   <?php if($ffIsSelected($opt['value'])): ?> checked <?php endif; ?>
                                   <?php if($required): ?> required <?php endif; ?>
                                   <?php if($disabled || $opt['disabled']): ?> disabled <?php endif; ?>
                                   <?php echo $opt['attrs']; ?>>
                            <label for="<?php echo e($opt['id']); ?>"><?php echo e($opt['label']); ?></label>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php break; ?>

            <?php case ('checkbox'): ?>
            <?php case ('switch'): ?>
                <label class="ff-field__check <?php echo e($type === 'switch' ? 'ff-field__check--switch' : ''); ?>">
                    <input
                        type="checkbox"
                        name="<?php echo e($name); ?>"
                        id="<?php echo e($id); ?>"
                        value="<?php echo e($value ?? 1); ?>"
                        class="<?php echo e($inputClass); ?>"
                        <?php if(!empty($checked)): ?> checked <?php endif; ?>
                        <?php if($required): ?> required <?php endif; ?>
                        <?php if($disabled): ?> disabled <?php endif; ?>
                        <?php echo $extraAttrs; ?>>
                    <span class="ff-field__check-label"><?php echo e($label); ?><?php if($required): ?><span class="ff-field__required">*</span><?php endif; ?></span>
                </label>
                <?php break; ?>

            <?php default: ?>
                <input
                    type="<?php echo e($type); ?>"
                    name="<?php echo e($name); ?>"
                    id="<?php echo e($id); ?>"
                    value="<?php echo e($value); ?>"
                    class="form-control ff-field__input <?php echo e($inputClass); ?>"
                    placeholder="<?php echo e($placeholder); ?>"
                    <?php if($maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
                    <?php if($minlength): ?> minlength="<?php echo e($minlength); ?>" <?php endif; ?>
                    <?php if($min !== null): ?> min="<?php echo e($min); ?>" <?php endif; ?>
                    <?php if($max !== null): ?> max="<?php echo e($max); ?>" <?php endif; ?>
                    <?php if($step !== null): ?> step="<?php echo e($step); ?>" <?php endif; ?>
                    <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?>
                    <?php if($required): ?> required <?php endif; ?>
                    <?php if($disabled): ?> disabled <?php endif; ?>
                    <?php if($readonly): ?> readonly <?php endif; ?>
                    <?php if($charCount && $maxlength): ?> data-char-count data-char-count-target="#<?php echo e($counterId); ?>" <?php endif; ?>
                    <?php echo $extraAttrs; ?>>
        <?php endswitch; ?>

        <?php if($suffix && !in_array($type, ['textarea','select','radio','checkbox','switch'])): ?>
            <span class="ff-field__affix ff-field__suffix"><?php echo e($suffix); ?></span>
        <?php endif; ?>

        <?php if($type === 'password'): ?>
            <span class="material-icons ff-field__toggle-password togglePassword"
                  data-ff-toggle-password="#<?php echo e($id); ?>">visibility_off</span>
        <?php endif; ?>
    </div>

    <div class="ff-field__footer">
        <div class="ff-field__messages">
            <?php if($hint): ?><small class="ff-field__hint"><?php echo e($hint); ?></small><?php endif; ?>
            <?php $__errorArgs = [$cleanName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="ff-field__error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <?php if($showCounter): ?>
            <small class="ff-field__counter">
                <span id="<?php echo e($counterId); ?>"><?php echo e(mb_strlen((string)($value ?? ''))); ?></span>/<?php echo e($maxlength); ?>

            </small>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /home/clean365-apii/htdocs/api.clean365.sa/resources/views/partials/_form-field.blade.php ENDPATH**/ ?>