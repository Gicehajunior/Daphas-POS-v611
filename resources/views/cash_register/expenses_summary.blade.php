<table class="table table-condensed">
    <thead>
        <tr>
            <th>@lang('Expense Category')</th>
            <th>@lang('Expense Amount')</th>
        </tr>
    </thead>

    <tbody>
        <?php if (!empty($expenses)): ?>

            <?php foreach ($expenses as $key => $value): ?>
                <tr>
                    <td>
                        {{ !empty($value['category']) ? $value['category'] : 'N/C' }}
                    </td>
                    <td>
                        <span class="display_currency" data-currency_symbol="true">
                            {{ $value['total_expense'] }}
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="2">
                    <strong>NB:</strong> @lang("No expenses incurred today")
                </td>
            </tr>

        <?php endif; ?>

        <?php if (!empty($commission_total_amount)): ?>
            <tr>
                <td>@lang("Commission Amount")</td>
                <td>
                    <span class="display_currency" data-currency_symbol="true">
                        {{ $commission_total_amount }}
                    </span>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
