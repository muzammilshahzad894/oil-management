{{-- Reusable calculator modal: Windows-style. Include on pages that have #btnSaleCalculator. --}}
<div class="modal fade sale-calculator-modal" id="saleCalculatorModal" tabindex="-1" aria-labelledby="saleCalculatorModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header py-2 bg-light border-bottom">
                <h6 class="modal-title mb-0" id="saleCalculatorModalLabel">
                    <i class="fas fa-calculator me-2 text-primary"></i>Calculator
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="calc-display-wrap mb-3 bg-light rounded px-2 py-2">
                    <div class="calc-expression text-end small text-muted font-monospace mb-1" id="calcExpression" style="min-height:1.25rem;"></div>
                    <input type="text" class="form-control form-control-lg text-end font-monospace border-0 bg-transparent calc-main-display" id="calcDisplay" readonly value="0">
                </div>
                <div class="row g-1 mb-1">
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-special py-2" id="calcCE">CE</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-special py-2" id="calcC">C</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-special py-2" id="calcBackspace" title="Backspace">⌫</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 calc-btn py-2" data-calc="/">÷</button></div>
                </div>
                <div class="row g-1">
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="7">7</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="8">8</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="9">9</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 calc-btn py-2" data-calc="*">×</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="4">4</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="5">5</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="6">6</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 calc-btn py-2" data-calc="-">−</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="1">1</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="2">2</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="3">3</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-primary w-100 calc-btn py-2" data-calc="+">+</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc="0">0</button></div>
                    <div class="col-3"><button type="button" class="btn btn-light border w-100 calc-btn py-2" data-calc=".">.</button></div>
                    <div class="col-3"><button type="button" class="btn btn-primary w-100 calc-btn py-2" data-calc="=">=</button></div>
                    <div class="col-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
