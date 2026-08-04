<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
