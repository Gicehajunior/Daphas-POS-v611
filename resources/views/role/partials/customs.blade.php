
@if(in_array('purchases', $enabled_modules) || in_array('stock_adjustment', $enabled_modules) || in_array('stock_transfer', $enabled_modules))
    <div class="row check_group mb-3">
        <div class="col-md-1">
            <h4>@lang('custom.role.stock_issuance')</h4>
        </div>

        <div class="col-md-3 d-flex align-items-center">
            <div class="form-check">
                <input type="checkbox" class="form-check-input check_all input-icheck" id="stock_issuance_select_all">
                <label class="form-check-label" for="stock_issuance_select_all">@lang('role.select_all')</label>
            </div>
        </div>

        <div class="col-md-8">
            @php
                $permissions = [
                    'view' => 'stock_issuance.view',
                    'create' => 'stock_issuance.create',
                    'update' => 'stock_issuance.update',
                    'delete' => 'stock_issuance.delete',
                ];
            @endphp

            @foreach($permissions as $key => $perm)
                <div class="form-check mb-1">
                    {!! Form::checkbox('permissions[]', $perm, in_array($perm, $role_permissions ?? []), [
                        'class' => 'form-check-input input-icheck',
                        'id' => 'perm_' . $key
                    ]) !!}
                    <label class="form-check-label" for="{{ 'perm_' . $key }}">
                        {{ __("custom." . $perm) }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
    <hr>
@endif
