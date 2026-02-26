<!-- Modal -->
<div class="modal fade" id="pinModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="pinModalLabel">PIN Input:</h5> 
        </div>
        <div class="modal-body pin-input-modal"> 
            <div class="pin-input">
                <div class="pin-box-container">
                    <input type="password" id="pinBox1" class="form-control pt-3 pb-2 mb-3 pin-box" maxlength="1" readonly>
                    <input type="password" id="pinBox2" class="form-control pt-3 pb-2 mb-3 pin-box" maxlength="1" readonly>
                    <input type="password" id="pinBox3" class="form-control pt-3 pb-2 mb-3 pin-box" maxlength="1" readonly>
                    <input type="password" id="pinBox4" class="form-control pt-3 pb-2 mb-3 pin-box" maxlength="1" readonly>
                    <input type="password" id="pinDisplay" class="form-control pt-3 pb-2 mb-3 pin-box" readonly> 
                </div> 
            </div>
            <div class="phone-keypad">
                <button class="btn btn-secondary pin-button" data-val="1">1</button>
                <button class="btn btn-secondary pin-button" data-val="2">2</button>
                <button class="btn btn-secondary pin-button" data-val="3">3</button>
                <button class="btn btn-secondary pin-button" data-val="4">4</button>
                <button class="btn btn-secondary pin-button" data-val="5">5</button>
                <button class="btn btn-secondary pin-button" data-val="6">6</button>
                <button class="btn btn-secondary pin-button" data-val="7">7</button>
                <button class="btn btn-secondary pin-button" data-val="8">8</button>
                <button class="btn btn-secondary pin-button" data-val="9">9</button>
                <button class="btn btn-secondary pin-button del-btn" data-val="del">⌫</button>
                <button class="btn btn-secondary pin-button" data-val="0">0</button>
                <button class="btn btn-secondary pin-button" data-val="enter">Enter</button>
            </div>
            <div class="modal-footer">
                <p class="text-center">
                    Daphas POS (Point of Sale) Secure Lock, Since @2024
                </p>
            </div>
        </div>  
    </div>
</div>
