<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/goals.php';
