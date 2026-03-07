class Contact extends Master {
    constructor() {
        super(); 
    }

    initializeContacts() {
        this.setupLedgerUi(); 
        this.setupContactsModalUX(); 
        this.editCustomer();
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

    /**
     * Handles Contact Category UX inside the Contacts Modal.
     *
     * Behaviour:
     * - Reads selected value from #contact_category
     * - Hides all contact sections inside the same modal
     * - Shows only the matching section
     */
    setupContactsModalUX() {
        const contact_category = $('#contact_category');
        if (!contact_category.length) return;
    
        const modal = contact_category.closest('.modal');
    
        function updateSections(value) {
    
            // Hide all sections
            modal.find('.contact-category-section').css('display', 'none');
    
            // Stop if value is empty
            if (!value) return;
    
            // Show selected section
            modal.find('.contact-category-section.' + value).css('display', 'block');
        }
    
        contact_category.on('change select2:select', function () {
            updateSections($(this).val());
        });
    
        // Initialize for edit forms
        const initialValue = contact_category.val();
        if (initialValue) {
            updateSections(initialValue);
        }
    }
} 