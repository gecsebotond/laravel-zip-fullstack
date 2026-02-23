<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\County;
use App\Models\Place;

// Redirect root to counties
Route::redirect('/', '/counties');

// 1. Main View & Alphabetical Filter (Public)
Route::get('/counties', function (Request $request) {
    $counties = County::all();
    $selectedCountyId = $request->query('county_id');
    $selectedInitial = $request->query('initial');

    $places = collect();
    $initials = collect();

    if ($selectedCountyId) {
        // Fetch initials for the selected county
        $initials = Place::where('county_id', $selectedCountyId)
            ->selectRaw('UPPER(LEFT(name, 1)) as initial')
            ->distinct()
            ->orderBy('initial')
            ->pluck('initial');

        // Fetch places based on county and optional initial
        $query = Place::where('county_id', $selectedCountyId);
        if ($selectedInitial) {
            $query->whereRaw('UPPER(LEFT(name, 1)) = ?', [$selectedInitial]);
        }
        $places = $query->orderBy('name')->get();
    }

    return view('counties', compact('counties', 'selectedCountyId', 'selectedInitial', 'initials', 'places'));
})->name('counties.index');

// 2. Authentication Routes
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return back();
    }
    return back()->withErrors(['email' => 'Invalid credentials']);
})->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return back();
})->name('logout');

// 3. Protected CRUD Routes for Places
Route::middleware('auth')->group(function () {
    Route::post('/places', function (Request $request) {
        $validated = $request->validate([
            'county_id' => 'required|exists:counties,id',
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);
        Place::create($validated);
        return back();
    })->name('places.store');

    Route::put('/places/{place}', function (Request $request, Place $place) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);
        $place->update($validated);
        return back();
    })->name('places.update');

    Route::delete('/places/{place}', function (Place $place) {
        $place->delete();
        return back();
    })->name('places.destroy');
});