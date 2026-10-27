(function () {
    'use strict';

    var activeModal = null;
    var previousFocus = null;
    var tooltip = null;
    var tooltipTarget = null;
    var activePopconfirm = null;
    var activeToast = null;

    function ensureToastRegion() {
        var region = document.querySelector('.tb-toast-region');
        if (!region) {
            region = document.createElement('div');
            region.className = 'tb-toast-region';
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('aria-atomic', 'false');
            document.body.appendChild(region);
        }
        return region;
    }

    function toast(message, type, duration) {
        if (!message) return;
        type = type || 'info';
        duration = typeof duration === 'number' ? duration : 4200;

        var region = ensureToastRegion();
        if (activeToast && activeToast.parentNode) {
            activeToast.parentNode.removeChild(activeToast);
        }
        var item = document.createElement('div');
        item.className = 'tb-toast tb-toast--' + type;
        item.setAttribute('role', type === 'error' ? 'alert' : 'status');

        var body = document.createElement('div');
        body.className = 'tb-toast__body';
        body.textContent = message;

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'tb-toast__close';
        close.setAttribute('aria-label', 'Dismiss notification');
        close.textContent = '×';
        close.addEventListener('click', function () { removeToast(item); });

        item.appendChild(body);
        item.appendChild(close);
        region.appendChild(item);
        activeToast = item;
        window.setTimeout(function () { removeToast(item); }, duration);
    }

    function removeToast(item) {
        if (!item || !item.parentNode) return;
        item.classList.add('is-leaving');
        if (activeToast === item) activeToast = null;
        window.setTimeout(function () {
            if (item.parentNode) item.parentNode.removeChild(item);
        }, 190);
    }

    function ensureTooltip() {
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'tbGlobalTooltip';
            tooltip.className = 'tb-tooltip';
            tooltip.setAttribute('role', 'tooltip');
            document.body.appendChild(tooltip);
        }
        return tooltip;
    }

    function positionTooltip(target) {
        if (!target || !tooltip) return;
        var rect = target.getBoundingClientRect();
        tooltip.style.left = '0px';
        tooltip.style.top = '0px';
        var tipRect = tooltip.getBoundingClientRect();
        var left = Math.max(8, Math.min(
            window.innerWidth - tipRect.width - 8,
            rect.left + (rect.width - tipRect.width) / 2
        ));
        var top = rect.top - tipRect.height - 8;
        if (top < 8) top = rect.bottom + 8;
        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    function showTooltip(target) {
        var text = target.getAttribute('data-tooltip');
        if (!text) return;
        tooltipTarget = target;
        var el = ensureTooltip();
        el.textContent = text;
        positionTooltip(target);
        el.classList.add('is-visible');
    }

    function hideTooltip() {
        if (tooltip) tooltip.classList.remove('is-visible');
        tooltipTarget = null;
    }

    function initTooltips(root) {
        (root || document).querySelectorAll('[data-tooltip], [data-tooltip-auto]').forEach(function (target) {
            if (target.dataset.tooltipReady === '1') return;
            if (target.hasAttribute('data-tooltip-auto')) {
                var overflowed = target.scrollWidth > target.clientWidth + 1;
                if (!overflowed) return;
                if (!target.getAttribute('data-tooltip')) {
                    target.setAttribute(
                        'data-tooltip',
                        target.getAttribute('data-tooltip-auto') || target.textContent.trim()
                    );
                }
            }
            target.dataset.tooltipReady = '1';
            target.setAttribute('aria-describedby', 'tbGlobalTooltip');
            target.addEventListener('mouseenter', function () { showTooltip(target); });
            target.addEventListener('focus', function () { showTooltip(target); });
            target.addEventListener('mouseleave', hideTooltip);
            target.addEventListener('blur', hideTooltip);
        });
    }

    function focusableIn(modal) {
        return Array.from(modal.querySelectorAll(
            'button:not([disabled]), a[href], input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (element) {
            return element.offsetParent !== null;
        });
    }

    function openModal(modal) {
        if (!modal) return;
        if (activeModal && activeModal !== modal) closeModal(activeModal);
        previousFocus = document.activeElement;
        activeModal = modal;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('tb-modal-open');
        window.setTimeout(function () {
            var focusable = focusableIn(modal);
            if (focusable.length) focusable[0].focus();
        }, 20);
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        if (activeModal === modal) {
            activeModal = null;
            document.body.classList.remove('tb-modal-open');
            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
            }
        }
    }

    function closePopconfirm() {
        if (activePopconfirm && activePopconfirm.parentNode) {
            activePopconfirm.parentNode.removeChild(activePopconfirm);
        }
        activePopconfirm = null;
    }

    function popconfirm(options) {
        closePopconfirm();
        options = options || {};
        var box = document.createElement('div');
        box.className = 'tb-popconfirm';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');

        var title = document.createElement('strong');
        title.className = 'tb-popconfirm__title';
        title.textContent = options.title || 'Confirm action';

        var message = document.createElement('p');
        message.className = 'tb-popconfirm__message';
        message.textContent = options.message || 'Are you sure?';

        var actions = document.createElement('div');
        actions.className = 'tb-popconfirm__actions';

        var cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn-secondary';
        cancel.textContent = 'Cancel';

        var confirm = document.createElement('button');
        confirm.type = 'button';
        confirm.className = 'btn-primary';
        confirm.textContent = options.confirmLabel || 'Confirm';

        cancel.addEventListener('click', closePopconfirm);
        confirm.addEventListener('click', function () {
            closePopconfirm();
            if (typeof options.onConfirm === 'function') options.onConfirm();
        });

        actions.appendChild(cancel);
        actions.appendChild(confirm);
        box.appendChild(title);
        box.appendChild(message);
        box.appendChild(actions);
        document.body.appendChild(box);

        var rect = options.anchor ? options.anchor.getBoundingClientRect() : {
            left: window.innerWidth - 300,
            bottom: 80,
            width: 0,
            top: 0
        };
        var boxRect = box.getBoundingClientRect();
        box.style.left = Math.max(10, Math.min(window.innerWidth - boxRect.width - 10, rect.left)) + 'px';
        box.style.top = Math.min(window.innerHeight - boxRect.height - 10, rect.bottom + 8) + 'px';
        activePopconfirm = box;
        confirm.focus();
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            hideTooltip();
            closePopconfirm();
            if (activeModal) closeModal(activeModal);
            return;
        }
        if (event.key === 'Tab' && activeModal) {
            var focusable = focusableIn(activeModal);
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    document.addEventListener('click', function (event) {
        if (activePopconfirm && !activePopconfirm.contains(event.target)) {
            closePopconfirm();
        }
        if (event.target.classList && event.target.classList.contains('modal-overlay')) {
            closeModal(event.target);
        }
    });

    window.addEventListener('resize', function () {
        hideTooltip();
        closePopconfirm();
    });
    window.addEventListener('scroll', hideTooltip, true);

    window.TeamBoxUI = {
        toast: toast,
        openModal: openModal,
        closeModal: closeModal,
        popconfirm: popconfirm,
        initTooltips: initTooltips
    };

    document.addEventListener('DOMContentLoaded', function () {
        initTooltips(document);
        if (window.TEACHER_FLASH && window.TEACHER_FLASH.message) {
            toast(window.TEACHER_FLASH.message, window.TEACHER_FLASH.type || 'info');
        }
    });
})();
