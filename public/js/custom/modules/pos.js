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
        // Any applicable constructor logic can go here
    }

    initializePos() {
        this.initEditCustomer();
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
        
        if (use_esd) {
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

    /**
     * Handles customer edit form submission via AJAX.
     *
     * - Attaches click listeners to `.edit-contact-submit-btn`
     * - Prevents default form submission
     * - Resolves the closest parent form dynamically
     * - Submits form data using Fetch API (supports Laravel method spoofing)
     * - Expects JSON response with `status: success|error`
     * - Displays toast notifications based on server response
     * - Automatically resets form and closes modal on success
     * - Ensures button state (disabled/loading) is safely restored via `finally`
     *
     * Dependencies:
     * cloneNodeElement, disableElement, enableElement,
     * toggleButtonContent, resolveClosestForm,
     * formRouteParser, resetFormAndCloseModal,
     * toast, route
     */
    editCustomer() {
        const btns = document.querySelectorAll('.edit-contact-submit-btn');
    
        btns.forEach(btn => {
            if (!btn) return;
    
            btn = cloneNodeElement(btn);
    
            btn.addEventListener('click', async (event) => {
                event.preventDefault();
    
                disableElement(btn);
                toggleButtonContent(btn);
    
                let form = resolveClosestForm(btn);
    
                if (!form) {
                    toast('error', 3000, lang.generic_error);
                    enableElement(btn);
                    toggleButtonContent(btn, '', true);
                    return;
                }

                const token = $('meta[name="csrf-token"]').attr('content') || '';
    
                let data = new FormData(form); 
                let action = formRouteParser(form);
    
                try {
                    const res = await fetch(action, {
                        method: "PUT",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest', 
                            'X-CSRF-TOKEN': token
                        },
                        body: jsonifyFormData(data)
                    });
    
                    let response;
    
                    try {
                        response = await res.json();
                    } catch (e) {
                        throw new Error(lang.invalid_json_exception);
                    }
    
                    if (response && response?.success === true) {
                        resetFormAndCloseModal(form);
                        toast('success', 5000, response.msg || lang.undefined_error);
                        return;
                    }
    
                    if (response && response?.success === false) {
                        toast('error', 5000, response.msg || lang.undefined_error);
                        return;
                    }
    
                    toast('error', 5000, lang.undefined_error);
    
                } catch (error) {
                    console.error(error.message || error);

                    toast(
                        'error',
                        5000,
                        error.message || 'Server error, Please try again or contact the administrator!'
                    );
    
                } finally {
                    enableElement(btn);
                    toggleButtonContent(btn, '', true);
                }
            });
        });
    }    
}

document.addEventListener('DOMContentLoaded', event => {
    const posIntance = new POS();
    posIntance.initializePos(); 
});