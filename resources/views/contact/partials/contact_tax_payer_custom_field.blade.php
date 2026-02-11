
<div class="form-group">
    <div class="input-group">
        <div class="checkbox"> 
            <label>
                {!! Form::checkbox('tax_payer', 1, 
                isset($contact->tax_exempted) ? $contact->tax_exempted : '', [ 'class' => 'input-icheck']); !!} {{ __( 'Tax Payer' ) }}
            </label>
        </div>
    </div>
</div>