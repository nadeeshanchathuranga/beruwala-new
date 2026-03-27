<?php

return [
    // Determines available cash used to validate till cash-out entries.
    // opening_plus_sales_plus_cash_in_minus_cash_out: opening + sales + cash in - cash out
    // opening_plus_cash_in_minus_cash_out: opening + cash in - cash out
    'available_cash_rule' => env('POS_AVAILABLE_CASH_RULE', 'opening_plus_sales_plus_cash_in_minus_cash_out'),
];
