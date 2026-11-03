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

    /* ---------- 暖白圆角自定义下拉（无 JS 时原生 select 兜底） ---------- */
    var sgOpenPanel = null;

    function sgClosePanel() {
        if (!sgOpenPanel) return;
        sgOpenPanel.hidden = true;
        if (sgOpenPanel._sgTrigger) {
            sgOpenPanel._sgTrigger.setAttribute('aria-expanded', 'false');
        }
        sgOpenPanel = null;
    }

    function sgBuildSelect(native) {
        if (native.dataset.sgSelect === '1') return;
        if (native.multiple || (native.size && native.size > 1)) return;
        native.dataset.sgSelect = '1';

        var wrap = document.createElement('div');
        wrap.className = 'sg-select';
        if (native.disabled) wrap.classList.add('is-disabled');

        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'sg-select__trigger';
        trigger.disabled = native.disabled;
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-haspopup', 'listbox');

        var panel = document.createElement('div');
        panel.className = 'sg-select__panel';
        panel.setAttribute('role', 'listbox');
        panel.hidden = true;
        panel._sgTrigger = trigger;

        var uid = 'sgc-' + Math.random().toString(36).slice(2, 9);
        trigger.id = uid;
        panel.setAttribute('aria-labelledby', uid);
        native.setAttribute('aria-hidden', 'true');

        native.insertAdjacentElement('afterend', wrap);
        wrap.appendChild(native);
        wrap.appendChild(trigger);
        document.body.appendChild(panel);

        function currentText() {
            var opt = native.options[native.selectedIndex];
            return opt ? opt.text : '';
        }
        function isPlaceholder() {
            var opt = native.options[native.selectedIndex];
            return !opt || opt.value === '' || native.classList.contains('is-placeholder');
        }
        function syncTrigger() {
            trigger.textContent = currentText();
            trigger.classList.toggle('is-placeholder', isPlaceholder());
        }
        function renderPanel() {
            panel.textContent = '';
            for (var i = 0; i < native.options.length; i++) {
                var opt = native.options[i];
                var item = document.createElement('div');
                item.className = 'sg-select__option';
                item.setAttribute('role', 'option');
                item.dataset.sgIndex = String(i);
                item.tabIndex = -1;
                item.textContent = opt.text;
                if (opt.value === '') item.classList.add('is-placeholder');
                if (i === native.selectedIndex) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected', 'true');
                } else {
                    item.setAttribute('aria-selected', 'false');
                }
                panel.appendChild(item);
            }
        }
        function focusOption(index, scroll) {
            var items = panel.querySelectorAll('.sg-select__option');
            for (var i = 0; i < items.length; i++) {
                items[i].classList.toggle('is-focused', i === index);
            }
            var el = items[index];
            if (el) {
                el.focus();
                if (scroll && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
            }
        }
        function openPanel(initialIndex) {
            if (native.disabled) return;
            if (sgOpenPanel && sgOpenPanel !== panel) sgClosePanel();
            renderPanel();
            panel.hidden = false;
            var rect = trigger.getBoundingClientRect();
            panel.style.left = '0px';
            panel.style.top = '0px';
            var pr = panel.getBoundingClientRect();
            var left = Math.max(10, Math.min(window.innerWidth - pr.width - 10, rect.left));
            var top = (rect.bottom + pr.height + 8 > window.innerHeight)
                    ? rect.top - pr.height - 8
                    : rect.bottom + 6;
            panel.style.left = left + 'px';
            panel.style.top = top + 'px';
            sgOpenPanel = panel;
            trigger.setAttribute('aria-expanded', 'true');
            focusOption(initialIndex !== undefined ? initialIndex : Math.max(0, native.selectedIndex), false);
        }
        function choose(index) {
            var opt = native.options[index];
            if (!opt) return;
            if (native.selectedIndex !== index) {
                native.selectedIndex = index;
                native.dispatchEvent(new Event('change', { bubbles: true }));
            }
            syncTrigger();
            sgClosePanel();
            trigger.focus();
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            if (sgOpenPanel === panel) { sgClosePanel(); return; }
            openPanel(native.selectedIndex);
        });
        trigger.addEventListener('keydown', function (event) {
            var key = event.key;
            if (key === 'ArrowDown' || key === 'ArrowUp' || key === 'Enter' || key === ' ') {
                event.preventDefault();
                if (sgOpenPanel === panel) {
                    var items = panel.querySelectorAll('.sg-select__option');
                    var idx = 0;
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].classList.contains('is-focused')) idx = i;
                    }
                    if (key === 'ArrowDown') focusOption(Math.min(items.length - 1, idx + 1), true);
                    else if (key === 'ArrowUp') focusOption(Math.max(0, idx - 1), true);
                } else {
                    openPanel(native.selectedIndex);
                }
            } else if (key === 'Escape') {
                sgClosePanel();
                trigger.focus();
            }
        });
        panel.addEventListener('click', function (event) {
            var item = event.target.closest ? event.target.closest('.sg-select__option') : null;
            if (item) choose(parseInt(item.dataset.sgIndex, 10));
        });
        panel.addEventListener('keydown', function (event) {
            var key = event.key;
            var items = panel.querySelectorAll('.sg-select__option');
            var idx = -1;
            for (var i = 0; i < items.length; i++) {
                if (items[i].classList.contains('is-focused')) idx = i;
            }
            if (key === 'ArrowDown') {
                event.preventDefault();
                focusOption(Math.min(items.length - 1, idx + 1), true);
            } else if (key === 'ArrowUp') {
                event.preventDefault();
                focusOption(Math.max(0, idx - 1), true);
            } else if (key === 'Home') {
                event.preventDefault();
                focusOption(0, true);
            } else if (key === 'End') {
                event.preventDefault();
                focusOption(items.length - 1, true);
            } else if (key === 'Enter' || key === ' ') {
                event.preventDefault();
                var focused = panel.querySelector('.sg-select__option.is-focused');
                if (focused) choose(parseInt(focused.dataset.sgIndex, 10));
            } else if (key === 'Escape') {
                event.preventDefault();
                sgClosePanel();
                trigger.focus();
            } else if (key === 'Tab') {
                sgClosePanel();
            }
        });
        native.addEventListener('change', syncTrigger);
        syncTrigger();
    }

    function sgInitSelects(root) {
        if (!document.body || !document.body.classList.contains('sg-teacher')) return;
        var selects = (root || document).querySelectorAll('select');
        for (var i = 0; i < selects.length; i++) {
            sgBuildSelect(selects[i]);
        }
        if ((root || document).querySelector('.sg-select')) {
            document.body.classList.add('sg-custom-selects');
        }
    }

    document.addEventListener('click', function (event) {
        if (!sgOpenPanel) return;
        var wrap = sgOpenPanel._sgTrigger.parentNode;
        if (!sgOpenPanel.contains(event.target) && !wrap.contains(event.target)) {
            sgClosePanel();
        }
    });
    window.addEventListener('scroll', sgClosePanel, true);
    window.addEventListener('resize', sgClosePanel);

    /* ---------- Dashboard 时钟 + 截止倒计时（每秒刷新） ---------- */
    function initDashboardTicker() {
        var clock = document.getElementById('tbClock');
        var clockDate = document.getElementById('tbClockDate');
        var cd = document.getElementById('tbCountdown');
        if (!clock && !cd) return;

        var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        var monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        function pad(n) { return (n < 10 ? '0' : '') + n; }

        function tickClock(now) {
            if (clock) {
                clock.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
            }
            if (clockDate) {
                clockDate.textContent = dayNames[now.getDay()] + ', ' + now.getDate() + ' ' + monthNames[now.getMonth()] + ' ' + now.getFullYear();
            }
        }

        function tickCountdown(now) {
            if (!cd) return;
            var target = parseInt(cd.getAttribute('data-target'), 10);
            if (!target) return;
            var start = parseInt(cd.getAttribute('data-start') || '0', 10);
            var remain = Math.max(0, target - now.getTime());
            var label = document.getElementById('tbCountdownLabel');
            if (remain <= 0) {
                cd.textContent = 'Due now';
                if (label) label.textContent = 'Deadline passed';
                return;
            }
            var d = Math.floor(remain / 86400000);
            var h = Math.floor((remain % 86400000) / 3600000);
            var m = Math.floor((remain % 3600000) / 60000);
            var s = Math.floor((remain % 60000) / 1000);
            cd.textContent = d + 'd ' + pad(h) + ':' + pad(m) + ':' + pad(s);
            var bar = document.getElementById('tbCountdownBar');
            if (bar && start > 0 && target > start) {
                var total = target - start;
                var elapsed = Math.max(0, now.getTime() - start);
                bar.style.width = Math.min(100, Math.round(elapsed / total * 100)) + '%';
            }
        }

        function tick() {
            var now = new Date();
            tickClock(now);
            tickCountdown(now);
        }
        tick();
        setInterval(tick, 1000);
    }

    window.TeamBoxUI = {
        toast: toast,
        openModal: openModal,
        closeModal: closeModal,
        popconfirm: popconfirm,
        initTooltips: initTooltips
    };

    document.addEventListener('DOMContentLoaded', function () {
        initTooltips(document);
        sgInitSelects(document);
        initDashboardTicker();
        var sgObserver = new MutationObserver(function () {
            sgInitSelects(document);
        });
        sgObserver.observe(document.body, { childList: true, subtree: true });
        if (window.TEACHER_FLASH && window.TEACHER_FLASH.message) {
            toast(window.TEACHER_FLASH.message, window.TEACHER_FLASH.type || 'info');
        }
    });
})();
