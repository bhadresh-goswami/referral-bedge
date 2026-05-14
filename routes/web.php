<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'bEdge Referral System is running';
});

Route::get('/thank-you', function () {
    return 'Thank you for your referral';
});

Route::post('/submit-referral', function () {
    return 'Referral submit route ready';
});
