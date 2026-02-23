<?php
use App\Http\Controllers\CountyController;
use App\Http\Controllers\PlaceController;

Route::get('/', function () {
    return redirect()->route('counties.index');
});

Route::resource('counties', CountyController::class);

// CSV download
Route::get('counties/{county}/download-csv',
    [CountyController::class, 'downloadCsv'])
    ->name('counties.downloadCsv');

// Places
Route::resource('counties.places', PlaceController::class);
