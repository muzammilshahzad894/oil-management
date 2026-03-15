/**
 * Sale calculator – Windows-style modal calculator.
 * Requires: jQuery, Bootstrap 5. Trigger: #btnSaleCalculator. Modal: #saleCalculatorModal.
 * All display/prev values kept as strings to avoid number+string coercion bugs.
 */
(function () {
    'use strict';

    function init() {
        var $modal = $('#saleCalculatorModal');
        var $display = $('#calcDisplay');
        var $expr = $('#calcExpression');
        if (!$modal.length || !$display.length) return;

        var state = {
            display: '0',
            prev: null,
            op: null,
            justComputed: false
        };

        function ensureString(val) {
            if (val === null || val === undefined) return '';
            return String(val);
        }

        function updateDisplay() {
            $display.val(state.display);
            if (state.prev !== null && state.op !== null) {
                $expr.text(state.prev + ' ' + state.op);
            } else {
                $expr.text('');
            }
        }

        function compute() {
            if (state.op === null || state.prev === null) return;
            var a = parseFloat(state.prev, 10);
            var b = parseFloat(state.display, 10);
            if (isNaN(a) || isNaN(b)) return;
            var r = 0;
            if (state.op === '+') r = a + b;
            else if (state.op === '-') r = a - b;
            else if (state.op === '*') r = a * b;
            else if (state.op === '/' && b !== 0) r = a / b;
            state.display = (r % 1 === 0) ? String(Math.round(r)) : String(r);
            state.prev = null;
            state.op = null;
            state.justComputed = true;
            $expr.text('');
        }

        function reset() {
            state.display = '0';
            state.prev = null;
            state.op = null;
            state.justComputed = false;
        }

        function clearEntry() {
            state.display = '0';
            state.justComputed = false;
        }

        function backspace() {
            state.justComputed = false;
            state.display = ensureString(state.display);
            if (state.display.length <= 1 || (state.display.length === 2 && state.display.charAt(0) === '-')) {
                state.display = '0';
            } else {
                state.display = state.display.slice(0, -1);
            }
        }

        $('#btnSaleCalculator').off('click.calc').on('click.calc', function () {
            reset();
            $expr.text('');
            $display.val('0');
            $modal.appendTo('body');
            var m = new bootstrap.Modal($modal[0], { backdrop: true, keyboard: true });
            m.show();
        });

        $modal.find('.calc-btn').off('click.calc').on('click.calc', function () {
            var c = ensureString($(this).data('calc'));
            state.display = ensureString(state.display);

            if (c === '=') {
                compute();
            } else if (c === '+' || c === '-' || c === '*' || c === '/') {
                state.justComputed = false;
                if (state.op !== null && state.prev !== null) compute();
                state.prev = state.display;
                state.op = c;
            } else if (c === '.') {
                state.justComputed = false;
                if (state.prev !== null && state.op !== null) {
                    if (state.display === state.prev || state.display === '0') state.display = '0.';
                    else if (state.display.indexOf('.') === -1) state.display += '.';
                } else if (state.display.indexOf('.') === -1) {
                    state.display += '.';
                }
            } else {
                if (state.justComputed) {
                    state.display = (c === '.' ? '0.' : c);
                    state.justComputed = false;
                } else if (state.prev !== null && state.op !== null) {
                    if (state.display === state.prev) state.display = (c === '0' ? '0' : c);
                    else if (state.display === '0' && c !== '0') state.display = c;
                    else state.display += c;
                } else if (state.display === '0' && c !== '.') {
                    state.display = (c === '0' ? '0' : c);
                } else {
                    state.display += c;
                }
            }
            updateDisplay();
        });

        $('#calcC').off('click.calc').on('click.calc', function () {
            reset();
            $expr.text('');
            updateDisplay();
        });

        $('#calcCE').off('click.calc').on('click.calc', function () {
            clearEntry();
            updateDisplay();
        });

        $('#calcBackspace').off('click.calc').on('click.calc', function () {
            backspace();
            updateDisplay();
        });
    }

    if (typeof jQuery !== 'undefined' && typeof bootstrap !== 'undefined') {
        jQuery(document).ready(init);
    }
})();
