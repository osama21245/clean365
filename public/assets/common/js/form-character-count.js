(function (global) {
    'use strict';

    var ATTR        = 'data-char-count';
    var TARGET_ATTR = 'data-char-count-target';
    var WARN_ATTR   = 'data-char-count-warn';
    var WARN_CLASS  = 'ff-field__counter--warn';
    var OVER_CLASS  = 'ff-field__counter--over';

    function resolveTarget(input) {
        var selector = input.getAttribute(TARGET_ATTR);
        if (selector) return document.querySelector(selector);

        var wrap = input.closest('[data-ff-field]');
        if (!wrap) return null;
        return wrap.querySelector('.ff-field__counter span');
    }

    function update(input) {
        var target = resolveTarget(input);
        if (!target) return;

        var len = (input.value || '').length;
        var max = parseInt(input.getAttribute('maxlength'), 10);

        target.textContent = String(len);

        var counterEl = target.closest('.ff-field__counter');
        if (!counterEl || !max || isNaN(max)) return;

        var warnRatio = parseFloat(input.getAttribute(WARN_ATTR)) || 0.8;
        var ratio = len / max;

        counterEl.classList.toggle(WARN_CLASS, ratio >= warnRatio && ratio < 1);
        counterEl.classList.toggle(OVER_CLASS, ratio >= 1);
    }

    function bind(input) {
        if (input.__ffCharCountBound) return;
        input.__ffCharCountBound = true;

        input.addEventListener('input', function () { update(input); });
        update(input);
    }

    function init(root) {
        var scope = root || document;
        var nodes = scope.querySelectorAll('[' + ATTR + ']');
        for (var i = 0; i < nodes.length; i++) bind(nodes[i]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }

    global.FormCharCount = { init: init, update: update };
})(window);
