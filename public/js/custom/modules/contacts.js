class Ledger extends Master {
    constructor() {
        super();
        // ANY CONSTRUCTOR LOGIC
    }

    initializeLedger() {
        this.setupLedgerUi();
    }

    /**
     * Called on the payment modal where
     * Payment Dues are processed 
     */
    setupLedgerUi() {
        // setup live sale computation for a due amount
        const amountToPay = document.querySelector(".amount-to-pay");
        const payment_line_amount = document.querySelector(".sale-due-amount");
        
        if (amountToPay && payment_line_amount) {
            amountToPay.addEventListener('change', () => {
              const realtime_gross_sale_due = document.querySelector(".real-time-sale-due");
              realtime_gross_sale_due.innerHTML = `KSh. ${(parseInt(payment_line_amount.value) - parseInt(amountToPay.value ? amountToPay.value : 0)).toLocaleString()}`;
            });
        } 

        // ...
    }
} 