<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CountyController;
use App\Models\Place;

Route::redirect('/', '/counties');

Route::get('/counties', [CountyController::class, 'index'])->name('counties.index');
Route::get('/counties/{county}/download-csv', [CountyController::class, 'downloadCsv'])->name('counties.downloadCsv');
Route::get('/counties/{county}/download-pdf', [CountyController::class, 'downloadPdf'])->name('counties.downloadPdf');

Route::middleware('auth')->group(function () {
    Route::post('/counties', [CountyController::class, 'store'])->name('counties.store');
    Route::put('/counties/{county}', [CountyController::class, 'update'])->name('counties.update');
    Route::delete('/counties/{county}', [CountyController::class, 'destroy'])->name('counties.destroy');
});


Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return back();
    }
    return back()->withErrors(['email' => 'Helytelen bejelentkezési adatok.']);
})->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return back();
})->name('logout');


Route::middleware('auth')->group(function () {
    Route::post('/places', function (Request $request) {
        $validated = $request->validate([
            'county_id' => 'required|exists:counties,id',
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);
        Place::create($validated);
        return back()->with('success', 'Település sikeresen hozzáadva!');
    })->name('places.store');

    Route::put('/places/{place}', function (Request $request, Place $place) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);
        $place->update($validated);
        return back()->with('success', 'Település frissítve!');
    })->name('places.update');

    Route::delete('/places/{place}', function (Place $place) {
        $place->delete();
        return back()->with('success', 'Település törölve!');
    })->name('places.destroy');
    Route::get('/counties/{county}/email-pdf', [CountyController::class, 'sendPdfEmail'])->name('counties.emailPdf');
});