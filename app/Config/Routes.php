<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('presensi', 'AttendanceController::index');
$routes->post('presensi/save', 'AttendanceController::save', ['filter' => 'csrf']);
