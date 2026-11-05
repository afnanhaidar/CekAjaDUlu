<?php

namespace Config;

$routes = Services::routes();

if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

$routes->get('presensi', 'AttendanceController::index');
$routes->post('presensi/save', 'AttendanceController::save', ['filter' => 'csrf']);
