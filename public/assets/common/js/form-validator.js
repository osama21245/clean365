(function (global) {
    'use strict';

    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;

    var pending = {};
    var bound   = {};

    function ffErrorPlacement(error, element) {
        if (element.hasClass('multi-image-validation-proxy')) {
            var wrapperSelector = element.data('upload-wrapper');
            var $warningBox = $(wrapperSelector).find('.global-multi-image-warning');
            $warningBox.html('').append(error);
            return;
        }
        if (element.is('input[type="file"]')) {
            var $gu = element.closest('.global-image-upload');
            if ($gu.length) { $gu.after(error); return; }
        }
        var $ffField = element.closest('[data-ff-field]');
        if ($ffField.length) {
            var $messages = $ffField.find('.ff-field__messages').first();
            if (!$messages.length) {
                $messages = $('<div class="ff-field__messages"></div>');
                $ffField.append($messages);
            }
            $messages.append(error);
            return;
        }
        var $inputWrap = element.closest('.input-wrap');
        if ($inputWrap.length) { $inputWrap.after(error); return; }
        element.after(error);
    }

    function ffInvalidHandler(event, validator) {
        var first = validator.errorList && validator.errorList[0];
        if (!first) return;
        var $el = $(first.element);
        if (!$el.length) return;

        var $target = $el;
        if ($el.hasClass('select2-hidden-accessible')) {
            $target = $el.next('.select2');
        } else if ($el.is('input[type="file"]')) {
            $target = $el.closest('.global-image-upload');
        } else {
            var $ffField = $el.closest('[data-ff-field]');
            if ($ffField.length) $target = $ffField;
        }

        if ($target.offset()) {
            $('html, body').animate({
                scrollTop: $target.offset().top - 140
            }, 300);
        }
    }

    function collectDeclarativeRules($form) {
        var rules = {};

        $form.find('[data-ff-match]').each(function () {
            var name = $(this).attr('name');
            var target = $(this).data('ff-match');
            if (!name || !target) return;
            rules[name] = rules[name] || {};
            rules[name].equalTo = target;
        });

        return rules;
    }

    function initForm($form) {
        if (!$.fn.validate) return false;
        var id = $form.attr('id') || '';
        if (bound[id]) return true;

        var custom = (id && pending[id]) || {};
        var declarativeRules = collectDeclarativeRules($form);

        $form.validate({
            ignore: custom.ignore || ':disabled,:hidden:not(.multi-image-validation-proxy)',
            errorPlacement: custom.errorPlacement || ffErrorPlacement,
            invalidHandler: custom.invalidHandler || ffInvalidHandler,
            submitHandler: custom.submitHandler,
            rules:    $.extend(true, {}, declarativeRules, custom.rules    || {}),
            messages: $.extend(true, {},                   custom.messages || {})
        });

        bound[id] = true;
        return true;
    }

    function initAll() {
        $('form[data-ff-validate]').each(function () {
            initForm($(this));
        });
    }

    global.FormValidator = {
        register: function (selectorOrId, options) {
            options = options || {};
            var $form = $(selectorOrId);
            var key = ($form.attr('id') || selectorOrId.replace(/^#/, ''));
            pending[key] = options;

            if (!$form.length) return;

            if (bound[key]) {
                var validator = $form.data('validator');
                if (!validator) { initForm($form); return; }

                if (options.submitHandler)  validator.settings.submitHandler  = options.submitHandler;
                if (options.invalidHandler) validator.settings.invalidHandler = options.invalidHandler;
                if (options.errorPlacement) validator.settings.errorPlacement = options.errorPlacement;
                if (options.ignore)         validator.settings.ignore         = options.ignore;

                if (options.rules) {
                    Object.keys(options.rules).forEach(function (name) {
                        var $field = $form.find('[name="' + name + '"]');
                        if ($field.length) {
                            $field.rules('add', options.rules[name]);
                        }
                    });
                }
                if (options.messages) {
                    validator.settings.messages = $.extend(
                        true, {}, validator.settings.messages || {}, options.messages
                    );
                }
                return;
            }

            initForm($form);
        },

        init: initAll,

        errorPlacement: ffErrorPlacement,
        invalidHandler: ffInvalidHandler
    };

    $(function () { initAll(); });

    $(document).on('invalid-form.validate', 'form[data-ff-validate]', function () {
        $(this).find('button[type="submit"]:disabled, [type="submit"]:disabled').each(function () {
            var $btn = $(this);
            var original = $btn.data('ffOriginalHtml');
            $btn.prop('disabled', false);
            if (original) $btn.html(original);
        });
    });
})(window);
