/**
 * ESD_DEVICE Class
 * Handles POS transactions and printing via ESD devices or fallback methods.
 * Uses Fetch API instead of jQuery for server requests.
 * 
 * Responsibilities:
 *  - Prepare transaction data
 *  - Post transaction to ESD device or bridging API
 *  - Handle response from ESD or bridge
 *  - Print receipts via printer or browser
 * 
 * @author Giceha
 * @version 2.0
 */
class ESD_DEVICE extends Master {
    /**
     * Class constructor initializes default properties
     */
    constructor() {
        super();

        // Transaction state
        this.txn = null; 
        this.transaction_id = undefined; 
        this.pos_settings = undefined;

        // Receipt / tax calculation state
        this.finalTotalWithoutShippingCharges = 0;
        this.total_before_tax = 0;
        this.total_tax_amount = 0;
        this.VAT_A = 0;
        this.VAT_A_Net = 0;
        this.exempted_product_total = 0;

        // Response and device state
        this.Message = undefined;
        this.TSIN = undefined;
        this.CUIN = undefined;
        this.QRCode = undefined;
        this.dtStmp = undefined;
        this.tims_post_transaction_error = undefined;
    }

    /**
     * Converts a VAT rate to the fraction of a total amount
     * that represents VAT (e.g., 16% of base = 13.7931% of total).
     *
     * @param {number} rate - VAT rate as percentage (e.g., 16 for 16%)
     * @returns {number} VAT fraction of total into 4 decimals by default, as percentage
     */
    vatFractionOfTotal(rate, decimals = 4) {
        rate = parseFloat(rate);
    
        if (isNaN(rate) || rate <= 0) return 0;
    
        const fraction = (rate / (100 + rate)) * 100;
        return parseFloat(fraction.toFixed(decimals));
    }    

    /**
     * Posts a transaction to the ESD device or bridging API.
     * Prepares product info, calculates taxes, and determines the endpoint URL.
     * 
     * @param {Object} transaction_result - The transaction result returned from POS backend.
     * @returns {void}
     */  
    async post_transaction(transaction_result) {
        if (!transaction_result?.transaction) return;

        this.txn = transaction_result.transaction;
        this.transaction_id = this.txn.id;
        this.pos_settings = transaction_result.pos_settings;

        const payment_method =
            Number(this.txn?.is_credit_sale ?? 0) === 0 ? 'cash' : 'credit';

        const saleType = this.txn.type || this.txn.sub_type;
        const _saleType =
            (saleType === 'sell' || !saleType) ? 'sales' : 'refund';

        const customer_pin =
            transaction_result?.customer_details?.business?.tax_number_1 || '';

        const tax_rates = transaction_result.tax_rates || [];
        const product_info = [];

        // ---- Work in cents (integer math) ----
        let totalCents = 0;
        let vatACents = 0;
        let vatANetCents = 0;

        for (const product of Object.values(this.txn.products || {})) {
            if (product.tax_exempted == 1 && product.tax_id?.length) {
                continue; // Skip exempted products
            } 

            const qty = Number(product.quantity ?? 1);

            const unitPrice = Number(
                String(product.unit_price_inc_tax ?? 0).replace(/,/g, '')
            );

            const discount = Number(
                String(product.line_discount_amount ?? 0).replace(/,/g, '')
            );

            const tax_rate =
                tax_rates.find(rate =>
                    Number(rate.id) === Number(product.tax_id)
                ) || {};

            const rate = Number(tax_rate.amount || 0);
            const effectiveVAT = this.vatFractionOfTotal(rate);

            // ---- Convert to cents ----
            const unitPriceCents = Math.round(unitPrice * 100);
            const discountCents = Math.round(discount * 100);

            const lineGrossCents = unitPriceCents * qty;
            const lineTotalCents = lineGrossCents - discountCents;

            totalCents += lineTotalCents;

            // ---- VAT calculation based on rounded line total ----
            if (effectiveVAT > 0) {
                const lineVatCents =
                    Math.round((effectiveVAT / 100) * lineTotalCents);

                vatACents += lineVatCents;
                vatANetCents += (lineTotalCents - lineVatCents);
            }

            product_info.push({
                productCode: effectiveVAT > 0
                    ? product.product_id
                    : "2835259626",

                productDesc: product.product_type,

                quantity: qty.toFixed(2),

                unitPrice: (unitPriceCents / 100),

                discount: (discountCents / 100),

                taxtype: rate
            });
        }

        if (totalCents <= 0) {
            this.txn.final_transaction = 1;
            this.finalizeTransaction(this.txn);
            return;
        }

        // ---- Final computed totals (from lines only) ----
        const total = totalCents / 100;
        const VAT_A = vatACents / 100;
        const VAT_A_Net = vatANetCents / 100;

        const transaction_data = {
            saleType: _saleType,
            cuin: "",
            till: "001",
            rctNo: this.txn.invoice_no,

            total: total,
            Paid: total,

            Payment: payment_method,
            CustomerPIN: customer_pin,

            VAT_A_Net: VAT_A_Net,
            VAT_A: VAT_A,

            VAT_B_Net: 0, VAT_B: 0,
            VAT_C_Net: 0, VAT_C: 0,
            VAT_D_Net: 0, VAT_D: 0,
            VAT_E_Net: 0, VAT_E: 0,
            VAT_F_Net: 0, VAT_F: 0,

            data: product_info
        };

        // ---- Determine endpoint ----
        const url = this.pos_settings.esd_api_bridger_endpoint
            ? `http://${this.pos_settings.esd_api_bridger_endpoint}/esd_api_bridger.php`
            : this.pos_settings.esd_device_endpoint
                ? `http://${this.pos_settings.esd_device_endpoint}:9000/api/values/PostTims`
                : "http://127.0.0.1:9000/api/values/PostTims";

        const data = url.includes('esd_api_bridger')
            ? {
                transaction_data,
                esd_device_endpoint: this.pos_settings.esd_device_endpoint
                    ? `http://${this.pos_settings.esd_device_endpoint}:9000/api/values/PostTims`
                    : "http://127.0.0.1:9000/api/values/PostTims"
            }
            : transaction_data;

        data.mode = this.pos_settings?.enable_live_mode
            ? 'live'
            : 'sandbox';

        const formData = new FormData();
        formData.append('data', JSON.stringify(data));

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const resp = await response.json();
            console.log('ESD RESPONSE:', resp);

            if (resp?.obj) {
                this.handle_tims_server_response(resp);
            } else {
                enable_pos_form_actions();
                toast('error', 4000, 'Invalid response from ESD bridge');
            }

        } catch (err) {
            console.error('ESD ERROR:', err);
            enable_pos_form_actions();
            toast('error', 4000, 'ESD connection failed');
        }
    }

    /**
     * Handles the response from ESD device or bridge API.
     * Shows toast on error or proceeds to finalize transaction.
     * 
     * @param {string} response - JSON string returned from server/device
     * @returns {void}
     */
    handle_tims_server_response(response) {
        if (!response) return;
        const resp = response
        const message = resp?.obj?.Message;
        this.tims_post_transaction_error = message;

        if (resp.status !== 'success') {
            enable_pos_form_actions();
            toast('error', 8000, message || 'Unexpected error! ESD printer connection error.');
            return;
        }

        const obj = resp?.obj;
        if (!obj || !this.txn) {
            enable_pos_form_actions();
            toast('error', 8000, 'ESD response invalid or transaction missing.');
            return;
        }

        // Extract explicitly (no dynamic mutation of this)
        const {
            Message,
            TSIN,
            CUIN,
            CUSN,
            QRCode,
            dtStmp
        } = obj;

        // Update transaction payload
        Object.assign(this.txn, {
            invoice_token: CUIN,
            ResponseCode: resp.status,
            Message,
            TSIN,
            CUIN,
            CUSN,
            QRCode,
            DtStmp: dtStmp,
            total_tax_amount: this.total_tax_amount,
            total_before_tax: this.total_before_tax
        });

        this.finalizeTransaction(this.txn);  
    } 

    /**
     * Finalizes transaction by sending the record onto the server
     * 
     * @param {Object} resp - Response object from the taxing pipeline.
     * @returns {void}
     */ 
    finalizeTransaction(txn) { 
        this.txn = txn;
        console.log('FINAL TXN TO POS:', this.txn);
        
        fetch('/pos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
            },
            body: JSON.stringify(this.txn)
        })
        .then(res => res.json().catch(() => ({})))
        .then(response => {
            this.extended_pos_print(response);
        })
        .catch(err => {
            console.error('POS update failed:', err);
            toast('error', 6000, 'Failed to update POS with ESD data.');
        });
    }
    
    /**
     * Extends POS printing for response from server
     * 
     * @param {string} resp - JSON response from POS
     */
    extended_pos_print(resp) {
        if (!resp) return;
        const data = resp
        this.bridge_print_invoice_func_call(data);
    }

    /**
     * Bridges the invoice printing call to printer or browser
     * 
     * @param {Object} resp - Response object with receipt data
     * @returns {void}
     */
    bridge_print_invoice_func_call(resp) {
        if (!resp) return;

        if (resp.success === 1) {
            if (resp.whatsapp_link && resp.pos_settings.enable_whatsapp_link) window.open(resp.whatsapp_link);
            $('#modal_payment').modal('hide');
            if (resp.receipt?.is_enabled) this.print_invoice(resp.receipt);

            if (window.location.href.includes('pos/create')) {
                reset_pos_form();
                toast('success', 8000, this.tims_post_transaction_error || resp.msg);
            }
        } else {
            toast('error', 8000, this.tims_post_transaction_error || resp.msg);
        }

        if (window.location.href.includes('pos/create')) enable_pos_form_actions();
    }

    /**
     * Prints invoice via printer websocket or browser print
     * 
     * @param {Object} receipt - Receipt object containing print_type and content
     * @returns {void}
     */
    print_invoice(receipt) {
        if (!receipt) return;

        if (receipt.print_type === 'printer') {
            const content = { ...receipt, type: 'print-receipt' };
            if (socket?.readyState === 1) socket.send(JSON.stringify(content));
            else {
                initializeSocket();
                setTimeout(() => socket.send(JSON.stringify(content)), 700);
            }
        } else if (receipt.html_content) {
            const title = document.title;
            if (receipt.print_title) document.title = receipt.print_title;
            const section = document.getElementById('receipt_section');
            if (section) {
                section.innerHTML = receipt.html_content;
                __currency_convert_recursively($(section));
                __print_receipt('receipt_section');
            }
            setTimeout(() => document.title = title, 1200);
        }
    }       
}
