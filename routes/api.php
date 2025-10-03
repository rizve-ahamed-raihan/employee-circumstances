<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrganizationEmployeeController;
use App\Http\Controllers\Api\EmployeeCocController;
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('organizations.employees', OrganizationEmployeeController::class);
Route::apiResource('employees.cocs', EmployeeCocController::class);