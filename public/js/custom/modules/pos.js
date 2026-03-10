/**
 * POS Class
 * 
 * Extends the Master class to handle POS-related operations.
 * 
 * Responsibilities:
 *  - Acts as a bridge between the frontend POS system and ESD_DEVICE
 *  - Handles printing invoices via ESD devices or fallback bridge methods
 *  - Provides a modern, reusable method for printing transactions (pos_print_util)
 * 
 * Usage:
 *  ```javascript
 *  const pos = new POS();
 *  pos.pos_print_util(transaction_result);
 *  ```
 * 
 * Notes:
 *  - The old `pos_print` function is deprecated. Use `pos_print_util` instead.
 *  - The `pos_print_util` method handles failed transactions, already final transactions,
 *    transactions with invoice tokens, and decides whether to use ESD_DEVICE or fallback printing.
 * 
 * @class
 * @extends Master
 */
class POS extends Master {
    constructor() {
        super();
        this.default_pos_lock_after_duration = document.querySelector('.default_pos_lock_after_duration');
        this.pinInput = '';
        this.inactivityTimer;
        this.locker_duration = this.default_pos_lock_after_duration?.value;
        this.pinDisplay = document.getElementById('pinDisplay');
        this.pinBoxes = document.querySelectorAll('.pin-box'); 
    }

    initializePos() {
        this.initEditCustomer();
        this.initializePosPinSecurity();
    }

    /**
     * New POS print / invoice handler
     * Handles printing via ESD_DEVICE or fallback bridge methods.
     * 
     * Guard clauses are used to handle:
     *  - Failed transactions
     *  - Missing transactions
     *  - Already finalized transactions
     *  - Transactions with existing invoice tokens
     * 
     * @param {Object} transaction_result - The transaction object from POS backend
     * @returns {void}
     */
    pos_print_util(transaction_result) {
        const esd_device_instance = new ESD_DEVICE();

        // 1. Handle failed transaction immediately
        if (!transaction_result.success) {
            toast('error', 8000, transaction_result.msg);

            if (window.location.href.includes('pos/create')) {
                enable_pos_form_actions();
            }
            return;
        }

        // 2. Helper function to decide how to print
        const printInvoice = () => {
            esd_device_instance.bridge_print_invoice_func_call(transaction_result);
        };

        // 3. If no transaction exists, fallback to printing
        if (!transaction_result.transaction) {
            printInvoice();
            return;
        }

        const txn = transaction_result.transaction;

        // 4. If transaction is already final, just print
        if (txn.final_transaction_status === 1) {
            printInvoice();
            return;
        }

        // 5. If invoice token exists, fallback to print
        if (txn.invoice_token) {
            printInvoice();
            return;
        }

        // 6. Use ESD device if allowed, else fallback
        const use_esd = transaction_result.pos_settings.enable_esd_usage?.length &&
                        transaction_result.customer_details.tax_exempted?.length !== 1;
        
        if (use_esd && !txn?.id) {
            esd_device_instance.post_transaction(transaction_result);
        } else {
            printInvoice();
        }
    }
    
    initEditCustomer() {
        const $buttons = $('.edit_customer');
        const $customer = $('#customer_id');
    
        if (!$customer.length) return; 

        // Disable buttons initially
        $buttons.prop('disabled', true);
    
        // Remove previous bindings to prevent duplication
        $customer.off('change select2:select');
    
        // Enable when customer changes (covers both normal change + Select2 selection)
        $customer.on('change select2:select', function () { 
            const value = $(this).val();
            
            // Disable if:
            // - empty
            // - null
            // - equals 96 (your restricted customer)
            if (!value || Number(value) === 96) {
                $buttons.prop('disabled', true);
                return;
            }

            // Otherwise enable
            $buttons.prop('disabled', false);
        });
    
        // Prevent duplicate click handlers
        $buttons.off('click').on('click', (e) => {
            const btn = e.currentTarget;
            const customerId = $('#customer_id').val();
        
            if (!customerId) return;
            this.assistiveModalActionParser(btn, `/contacts/${customerId}/edit`, [
                {callback: this.editCustomer}
            ]);
        });
    }

    initializePosPinSecurity() {  
        this.lockCheck(); 
        this.NumClicks(); 
        this.lockPos(); 
        this.disableManualInput(); 
    }
    
    showPinInputModal() {   
        this.ClearBoxInput(); 

        var myModal = document.getElementById('pinModal');

        if ($(myModal).hasClass('show')) {
            this.lockCheck();
        } else {
            $('#pinModal').modal('show'); 
        }

        this.disableManualInput();
    }

    resetInactivityTimer(locker_duration) { 
        var locker_duration = (locker_duration == undefined) ? 1000 : parseFloat(`${locker_duration}000`);
        // console.log(locker_duration);
        clearTimeout(this.inactivityTimer);
        this.inactivityTimer = setTimeout(() => {
            this.showPinInputModal(locker_duration);
        }, locker_duration);
    }

    detectInactivity(locker_duration) {
        console.log(locker_duration); 
        
        document.addEventListener('mousemove', (event) => {
            this.resetInactivityTimer(locker_duration);
        });

        document.addEventListener('keypress', () => {
            this.resetInactivityTimer(locker_duration);
        });

        // Initialize the timer
        this.resetInactivityTimer(locker_duration);
    }

    lockPos() {
        const lock_pos_btns = document.querySelectorAll('.lock_pos');
        
        lock_pos_btns.forEach(lock_pos_btn => {
            lock_pos_btn.addEventListener('click', event => {
                this.lockCheck();  
            });
        }); 
    }

    HandleKeypadEvents() {
        document.addEventListener('keydown', (event) => { 
            if (event.key >= '0' && event.key <= '9') {
                this.appendPin(event.key);
            } else if (event.key === 'Backspace') {
                this.deleteLastDigit();
            } else if (event.key === 'Enter') {
                this.enterPin();
            } else if (event.ctrlKey && event.key === 'L') {
                console.log(event)
                this.lockCheck();
            }
        });
    }

    disableManualInput() {
        document.querySelectorAll('.pin-box').forEach(box => {
            box.addEventListener('input', () => { 
                box.value = '';
            });
        });
    }

    ClearBoxInput() { 
        for (let i = 0; i < 5; i++) {
            document.querySelector('.del-btn').click();
        }
    }

    closeModal() {
        $('#pinModal').modal('hide');  
    }

    NumClicks() { 
        const pinButtons = document.querySelectorAll('.pin-button');

        this.HandleKeypadEvents(); 

        pinButtons.forEach(pinButton => {
            pinButton.addEventListener('click', event => {
                const buttonValue = event.target.getAttribute('data-val');

                if (buttonValue == 'del') {
                    this.deleteLastDigit();
                }
                else if (buttonValue == 'enter') {
                    // buttonValue.disabled = true;
                    this.enterPin();
                } 
                else {
                    this.appendPin(buttonValue); 
                }
            });
        });

        this.disableManualInput();
    }

    appendPin(number) {
        if (this.pinInput.length < this.pinBoxes.length) {
            this.pinInput += number;
            this.updatePinDisplay(); 
        }
    }

    deleteLastDigit() {
        this.pinInput = this.pinInput.slice(0, -1);
        this.updatePinDisplay(); 
    }

    enterPin() {
        var count = 0; 
        this.pinBoxes.forEach(box => { 
            if (box.value.length == 0)
            {
                count += 1;
            } 
        }); 

        if (parseInt(this.pinInput) && count == 0) {  
            this.authenticate(this.pinInput);
        }
        else if (count > 0) {
            toast('error', 3000, "Please fill in correct pin to unlock POS!");   
        }  
        else {
            toast('error', 3000, "Please fill in your pin to unlock POS!");  
        } 
    }

    updatePinDisplay() {
        this.pinDisplay.value = this.pinInput;
        this.pinBoxes.forEach((box, index) => {
            box.value = this.pinInput[index] || '';
        });
    }

    authenticate(pin) {
        clearTimeout(this.inactivityTimer);
    
        fetch('/pos/auth/pin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ pin: pin })
        })
        .then(res => res.json())
        .then((response) => {
            if (!response) {
                toast('error', 3000, "Please fill correct pin to unlock POS!");
                return;
            }
    
            if (response.status === 'success') {
                if (response.pin_disabled === false) {
                    (new POS()).closeModal();
    
                    (new POS()).detectInactivity(
                        response.locker_duration === undefined ? 1 : response.locker_duration
                    );
                    
                    toast(response.status, 5000, response.message);
    
                } else {
                    toast(response.status, 3000, "POS Pin is Disabled. Please enable to unlock!");
                }
    
            } else {
                toast(response.status, 3000, response.message);
            }
        })
        .catch((error) => {
            toast('error', 8000, "Unlock unsuccessfull. Please check your pin, make sure you are authorized to use POS.");
        });
    }

    lockCheck() {
        clearTimeout(this.inactivityTimer);
    
        fetch('/pos/auth/checkPinIfEnabled', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(response => {
            if (!response) return;
    
            if (response.status === 'success') {
                if (response.pin_disabled === false) {
                    (new POS()).showPinInputModal();
                }
            }
        })
        .catch(() => {
            console.error("lockCheck failed");
        });
    }     
}

document.addEventListener('DOMContentLoaded', event => {
    const posIntance = new POS();
    posIntance.initializePos(); 
});