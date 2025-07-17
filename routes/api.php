<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;


Route::get('/provinces', function () {
    return Province::all();
});

Route::get('/cities/{provinceId}', function ($provinceId) {
    $province = Province::findOrFail($provinceId);
    return $province->regencies;
});

Route::get('/districts/{cityId}', function ($cityId) {
    $city = Regency::findOrFail($cityId);
    return $city->districts;
});

Route::get('/villages/{districtId}', function ($districtId) {
    $district = District::findOrFail($districtId);
    return $district->villages;
});
