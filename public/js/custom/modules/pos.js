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
}
