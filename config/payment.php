<?php
return [
    'fees' => [
        'gopay'         => ['type' => 'percent', 'value' => 2.0],
        'qris'          => ['type' => 'percent', 'value' => 0.7],
        'bank_transfer' => ['type' => 'flat',    'value' => 4000],
        'credit_card'   => ['type' => 'percent', 'value' => 2.9, 'flat_add' => 2000],
    ],
];
