/**
 * index.js - front-end behaviour for Lost & Found (plain JavaScript, no libraries).
 *
 * Features
 *   1. Mobile menu button (☰) opens / closes the navigation.
 *   2. Flash messages can be dismissed and success messages fade away.
 *   3. Any <form data-confirm="..."> asks "Are you sure?" before it is sent
 *      (used for every delete / withdraw button).
 *   4. Image upload: instant preview + size and type check.
 *   5. Live character counter for the description box.
 *   6. Browse page: filters apply as soon as a drop-down changes.
 *   7. Client-side validation for the register, report and student forms
 *      (the server validates again - this is only for faster feedback).
 *   8. Browse page: instant search while typing (cards are filtered in the browser;
 *      pressing Enter still runs the full PHP/MySQL search).
 *   9. Table cells get data-label attributes so the CSS can show them as
 *      stacked "cards" on small screens.
 */
(function () {
    'use strict';

    var MAX_IMAGE_BYTES = 1000000;   // 1 MB (XAMPP's MySQL default packet size)
    var IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /* 1. Mobile navigation ------------------------------------------------ */
    var header = document.getElementById('site-header');
    var toggle = document.getElementById('menu-toggle');
    if (header && toggle) {
        toggle.addEventListener('click', function () {
            var open = header.classList.toggle('nav-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* 2. Flash messages --------------------------------------------------- */
    document.querySelectorAll('.flash').forEach(function (box) {
        var close = box.querySelector('.flash-close');
        if (close) {
            close.addEventListener('click', function () { box.remove(); });
        }
        if (box.classList.contains('flash-success')) {
            setTimeout(function () { box.classList.add('flash-hide'); }, 5000);
            setTimeout(function () { box.remove(); }, 5600);
        }
    });

    /* 3. Confirm before dangerous actions -------------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    /* 4. Image preview and validation ------------------------------------ */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        var preview = document.getElementById(input.getAttribute('data-preview'));

        // Shows the picture, or hides it and explains the problem in plain text.
        function show(file) {
            if (preview) {
                preview.src = URL.createObjectURL(file);
                preview.hidden = false;
            }
        }
        function fail(message) {
            input.value = '';
            if (preview) { preview.hidden = true; }
            input.setCustomValidity(message);
            input.reportValidity();
            input.setCustomValidity('');
        }

        // Big phone photos are shrunk in the browser (max 1280 px, JPEG) so they fit the 1 MB limit.
        function shrink(file) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                var scale = Math.min(1, 1280 / Math.max(img.width, img.height));
                var canvas = document.createElement('canvas');
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                canvas.toBlob(function (blob) {
                    if (!blob || blob.size > MAX_IMAGE_BYTES || typeof DataTransfer === 'undefined') {
                        fail('The image must be smaller than 1 MB. Please choose a smaller picture.');
                        return;
                    }
                    var small = new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                    var list = new DataTransfer();
                    list.items.add(small);
                    input.files = list.files;
                    show(small);
                }, 'image/jpeg', 0.8);
            };
            img.onerror = function () { fail('This picture could not be read. Please choose a JPG or PNG image.'); };
            img.src = url;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            input.setCustomValidity('');
            if (preview) { preview.hidden = true; }
            if (!file) { return; }
            if (IMAGE_TYPES.indexOf(file.type) === -1) {
                fail('Please choose a JPG, PNG, GIF or WebP image.');
                return;
            }
            if (file.size > MAX_IMAGE_BYTES) {
                shrink(file);
                return;
            }
            show(file);
        });
    });

    /* 5. Character counter ------------------------------------------------ */
    document.querySelectorAll('textarea[data-counter]').forEach(function (area) {
        var counter = document.getElementById(area.getAttribute('data-counter'));
        function update() { if (counter) { counter.textContent = area.value.length; } }
        area.addEventListener('input', update);
        update();
    });

    /* 6. Auto-apply browse filters --------------------------------------- */
    document.querySelectorAll('#filter-form .filter-select').forEach(function (select) {
        select.addEventListener('change', function () { select.form.submit(); });
    });

    /* 7. Form validation -------------------------------------------------- */
    var register = document.querySelector('form[data-validate="register"]');
    if (register) {
        var pass = register.querySelector('[name="u_pass"]');
        var pass2 = register.querySelector('[name="u_pass2"]');
        var checkMatch = function () {
            pass2.setCustomValidity(pass.value !== pass2.value ? 'The two passwords do not match.' : '');
        };
        pass.addEventListener('input', checkMatch);
        pass2.addEventListener('input', checkMatch);
    }

    var report = document.querySelector('form[data-validate="report"]');
    if (report) {
        report.addEventListener('submit', function (event) {
            var fields = ['title', 'location', 'description'];
            for (var i = 0; i < fields.length; i++) {
                var field = report.elements[fields[i]];
                if (field && field.value.trim() === '') {
                    field.setCustomValidity('This field cannot be empty.');
                    field.reportValidity();
                    field.addEventListener('input', function () { this.setCustomValidity(''); }, { once: true });
                    event.preventDefault();
                    return;
                }
            }
        });
    }

    /* 8. Instant search on the browse page ------------------------------- */
    var live = document.querySelector('input[data-live-search]');
    if (live) {
        var grid = document.getElementById(live.getAttribute('data-live-search'));
        var counter = document.getElementById('result-count');
        live.addEventListener('input', function () {
            if (!grid) { return; }
            var term = live.value.trim().toLowerCase();
            var shown = 0;
            grid.querySelectorAll('.item-card').forEach(function (card) {
                var match = term === '' || card.textContent.toLowerCase().indexOf(term) !== -1;
                card.hidden = !match;
                if (match) { shown++; }
            });
            if (counter) { counter.textContent = shown + ' item' + (shown === 1 ? '' : 's') + ' found'; }
        });
    }

    /* 9. Responsive tables ------------------------------------------------ */
    document.querySelectorAll('table').forEach(function (table) {
        var labels = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
            return th.textContent.trim();
        });
        table.querySelectorAll('tbody tr').forEach(function (row) {
            Array.prototype.forEach.call(row.children, function (cell, i) {
                if (labels[i]) { cell.setAttribute('data-label', labels[i]); }
            });
        });
    });
}());
