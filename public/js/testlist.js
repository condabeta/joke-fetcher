(function () {
    'use strict';

    // Show only inputs whose `name` attribute contains the value of the selected
    // option in the "Тип" (#type) select. The select itself is always visible.
    //
    // Algorithm choice:
    //   On every change of #type we walk every [name] field once and toggle a
    //   `.hidden` class by checking `name.indexOf(value) !== -1`. The work is
    //   O(N) in the number of fields, runs only on change, and uses a single
    //   class toggle (no layout thrash from rebuilding DOM).
    //
    // Alternatives considered:
    //   1) Rebuild the form on each change from a JS-side schema (the previous
    //      dynamic-fields.js approach). Rejected: throws away user input on
    //      every change, duplicates the field list in JS, and the task asks us
    //      to filter the existing markup, not to generate it.
    //   2) querySelectorAll(`[name*="${value}"]`) to pick the visible set, then
    //      another query for the hidden set. Rejected: needs CSS-escape on the
    //      value to be safe, and we still iterate all fields — no speed win.
    //   3) jQuery + :contains-style filters. Rejected: pulling in a library
    //      for one indexOf check is overkill; vanilla JS is enough here.

    function init() {
        var typeSelect = document.getElementById('type');
        if (!typeSelect) {
            return;
        }

        // The set of fields we manage = every element with a [name], minus the
        // select itself. Cached once; the form markup is static.
        var fields = Array.prototype.slice
            .call(document.querySelectorAll('[name]'))
            .filter(function (el) { return el !== typeSelect; });

        function apply() {
            var value = typeSelect.value || '';
            fields.forEach(function (el) {
                var name = el.getAttribute('name') || '';
                var match = value !== '' && name.indexOf(value) !== -1;
                var row = el.closest('.row') || el;
                row.classList.toggle('hidden', !match);
            });
        }

        typeSelect.addEventListener('change', apply);
        apply();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
