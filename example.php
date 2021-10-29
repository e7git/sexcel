<?php

if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
} else {

    function autoload($class)
    {
        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        require_once str_replace('Sayhey/SExcel', 'src', $file);
    }

    spl_autoload_register('autoload');
}

\Sayhey\SExcel\Writer::config(['publicDir' => __DIR__ . '///']);

$rows = [
    [
        'id' => 1,
        'order_no' => '202100000000001',
        'price' => 0.56
    ],
    [
        'id' => 2,
        'order_no' => '202100000000002',
        'price' => 12.5
    ],
    [
        'id' => 3,
        'order_no' => '202100000000003',
        'price' => 5.69
    ]
];

$format = [
    'id' => ['订单ID', '0'],
    'order_no' => '订单号',
    'price' => ['订单金额', '0.00']
];

list($ret, $filename) = \Sayhey\SExcel\Writer::public($rows, $format, '订单示例');
var_dump($ret);
var_dump($filename);
