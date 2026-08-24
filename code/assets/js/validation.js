/* ============================================================
   Client-side validation.
   Mirrors the server-side rules in includes/functions.php so the
   user gets instant feedback; PHP still re-validates everything.
   ============================================================ */

(function () {
    'use strict';

    var NAME_RE  = /^[A-Za-z ]{3,20}$/;
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/;
    var PHONE_RE = /^[0-9]{1,11}$/;
    var SUPPLIER_RE = /^[A-Za-z0-9 .,&()\/'-]+$/;

    /* ---------------- helpers ---------------- */

    function field(form, name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function showError(input, message) {
        if (!input) { return; }
        var box = input.form.querySelector('[data-error-for="' + input.name + '"]');
        if (box) { box.textContent = message || ''; }
        input.classList.toggle('invalid', !!message);
    }

    function clearError(input) {
        showError(input, '');
    }

    /**
     * Runs a list of {name, check} rules. check() returns an error string or ''.
     * Returns true when every rule passes, and focuses the first bad field.
     */
    function runRules(form, rules) {
        var firstBad = null;

        rules.forEach(function (rule) {
            var input = field(form, rule.name);
            if (!input) { return; }

            var message = rule.check(input.value, form) || '';
            showError(input, message);

            if (message && !firstBad) { firstBad = input; }
        });

        if (firstBad) { firstBad.focus(); }

        return firstBad === null;
    }

    /** Re-validates one field as the user types / leaves it. */
    function liveCheck(form, rules) {
        rules.forEach(function (rule) {
            var input = field(form, rule.name);
            if (!input) { return; }

            var handler = function () {
                showError(input, rule.check(input.value, form) || '');
            };

            input.addEventListener('blur', handler);
            input.addEventListener('input', function () {
                if (input.classList.contains('invalid')) { handler(); }
            });
            input.addEventListener('change', handler);
        });
    }

    function attach(form, rules) {
        if (!form) { return; }

        form.setAttribute('novalidate', 'novalidate');
        liveCheck(form, rules);

        form.addEventListener('submit', function (event) {
            if (!runRules(form, rules)) {
                event.preventDefault();
            }
        });
    }

    /* ---------------- 1. Registration ---------------- */

    attach(document.getElementById('registerForm'), [
        {
            name: 'full_name',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Full Name is required.'; }
                if (!NAME_RE.test(v)) {
                    return 'Full Name must contain only alphabets and spaces (3-20 characters).';
                }
                return '';
            }
        },
        {
            name: 'email',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Email Address is required.'; }
                if (!EMAIL_RE.test(v)) {
                    return 'Please enter a valid email address (example: user@domain.com).';
                }
                return '';
            }
        },
        {
            name: 'password',
            check: function (v) {
                if (!v) { return 'Password is required.'; }
                if (v.length < 8) { return 'Password must contain at least 8 characters.'; }
                return '';
            }
        },
        {
            name: 'confirm_password',
            check: function (v, form) {
                if (!v) { return 'Please confirm your password.'; }
                if (v !== field(form, 'password').value) {
                    return 'Confirm Password does not match the Password.';
                }
                return '';
            }
        },
        {
            name: 'phone',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Phone Number is required.'; }
                if (!PHONE_RE.test(v)) {
                    return 'Phone Number must contain only digits (maximum 11 digits).';
                }
                return '';
            }
        }
    ]);

    /* ---------------- 2. Login ---------------- */

    attach(document.getElementById('loginForm'), [
        {
            name: 'email',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Email Address is required.'; }
                if (!EMAIL_RE.test(v)) { return 'Please enter a valid email address.'; }
                return '';
            }
        },
        {
            name: 'password',
            check: function (v) {
                return v ? '' : 'Password is required.';
            }
        }
    ]);

    /* ---------------- 3. Add material ---------------- */

    attach(document.getElementById('materialForm'), [
        {
            name: 'material_name',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Material Name is required.'; }
                if (v.length > 100) { return 'Material Name cannot exceed 100 characters.'; }
                return '';
            }
        },
        {
            name: 'category',
            check: function (v) {
                return v ? '' : 'Please select a category.';
            }
        },
        {
            name: 'unit_price',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Unit Price is required.'; }
                if (isNaN(Number(v))) { return 'Unit Price must be a number.'; }
                if (Number(v) <= 0) { return 'Unit Price must be greater than zero.'; }
                return '';
            }
        },
        {
            name: 'available_quantity',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Available Quantity is required.'; }
                if (isNaN(Number(v))) { return 'Available Quantity must be a number.'; }
                if (Number(v) < 0) { return 'Available Quantity cannot be negative.'; }
                return '';
            }
        },
        {
            name: 'unit_of_measurement',
            check: function (v) {
                return v ? '' : 'Please select a unit of measurement.';
            }
        },
        {
            name: 'description',
            check: function (v) {
                return v.length > 500 ? 'Description should not exceed 500 characters.' : '';
            }
        }
    ]);

    /* ---------------- 4. Search ---------------- */

    attach(document.getElementById('searchForm'), [
        {
            name: 'q',
            check: function (v) {
                return v.trim() ? '' : 'Please enter a material name to search.';
            }
        }
    ]);

    /* ---------------- 5. Supplier name ---------------- */

    attach(document.getElementById('supplierForm'), [
        {
            name: 'supplier_name',
            check: function (v) {
                v = v.trim();
                if (!v) { return 'Supplier Name is required.'; }
                if (v.length < 3) { return 'Supplier Name must contain at least 3 characters.'; }
                if (v.length > 100) { return 'Supplier Name cannot exceed 100 characters.'; }
                if (!SUPPLIER_RE.test(v)) {
                    return 'Supplier Name may contain letters, digits, spaces and . , & ( ) / - \' only.';
                }
                return '';
            }
        }
    ]);

    /* ---------------- Description character counter ---------------- */

    var description = document.getElementById('description');
    var counter     = document.getElementById('descCount');

    if (description && counter) {
        var updateCount = function () {
            counter.textContent = description.value.length + ' / 500 characters';
        };
        description.addEventListener('input', updateCount);
        updateCount();
    }
}());
