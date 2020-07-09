<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use bkilshaw\EZStripe\EZStripe;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('ezstripe/checkout', [EZStripe::class, 'checkout'])->name('ezstripe.checkout');
Route::get('ezstripe/billing_portal', [EZStripe::class, 'billing_portal'])->name('ezstripe.billing_portal');
