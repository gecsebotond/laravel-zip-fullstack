<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\Place;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\CountyReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class CountyController extends Controller
{
    public function index(Request $request)
    {
        $counties = County::all();
        $selectedCountyId = $request->query('county_id');
        $selectedInitial = $request->query('initial');
        $searchQuery = $request->query('search');

        $places = collect();
        $initials = collect();

        // Ha van kiválasztott megye
        if ($selectedCountyId) {
            $initials = Place::where('county_id', $selectedCountyId)
                ->selectRaw('UPPER(LEFT(name, 1)) as initial')
                ->distinct()
                ->orderBy('initial')
                ->pluck('initial');

            // Betöltjük a megyét is (with('county')) a táblázat miatt
            $query = Place::with('county')->where('county_id', $selectedCountyId);
            
            if ($selectedInitial) {
                $query->whereRaw('UPPER(LEFT(name, 1)) = ?', [$selectedInitial]);
            }

            if ($searchQuery) {
                $query->where(function($q) use ($searchQuery) {
                    $q->where('name', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('postal_code', 'LIKE', '%' . $searchQuery . '%');
                });
            }

            $places = $query->orderBy('name')->get();
        } 
        // Ha NINCS kiválasztott megye, de VAN keresőszó (Globális keresés)
        elseif ($searchQuery) {
            $places = Place::with('county')
                ->where('name', 'LIKE', '%' . $searchQuery . '%')
                ->orWhere('postal_code', 'LIKE', '%' . $searchQuery . '%')
                ->orderBy('name')
                ->get();
        }

        return view('counties', compact('counties', 'selectedCountyId', 'selectedInitial', 'initials', 'places', 'searchQuery'));
    }

    public function downloadCsv(Request $request, County $county)
    {
        $initial = $request->query('initial');
        
        $query = $county->places();
        if ($initial) {
            $query->whereRaw('UPPER(LEFT(name, 1)) = ?', [$initial]);
        }
        $places = $query->orderBy('name')->get();

        $csvFile = fopen('php://memory', 'w');
        
        fputcsv($csvFile, ['county_id', 'county_name', 'place_id', 'place_name', 'postal_code'], ";");

        if ($places->isEmpty()) {
            fputcsv($csvFile, [$county->id, $county->name, '', '', ''], ";");
        } else {
            foreach ($places as $place) {
                fputcsv($csvFile, [
                    $county->id,
                    $county->name,
                    $place->id,
                    $place->name,
                    $place->postal_code
                ], ";");
            }
        }

        rewind($csvFile);
        $csvData = stream_get_contents($csvFile);
        fclose($csvFile);

        $csvData = "\xEF\xBB\xBF" . $csvData;

        return response($csvData, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="county_'.$county->id.'.csv"');
    }

    public function downloadPdf(Request $request, County $county)
    {
        $initial = $request->query('initial');
        
        $query = $county->places();
        if ($initial) {
            $query->whereRaw('UPPER(LEFT(name, 1)) = ?', [$initial]);
        }
        
        $data = [
            'county' => $county,
            'places' => $query->orderBy('name')->get()
        ];

        $pdf = Pdf::loadView('pdf.countypdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('county_' . $county->id . '.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:counties,name',
        ]);

        County::create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Megye sikeresen létrehozva!');
    }

    public function update(Request $request, $id)
    {
        $county = County::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:counties,name,' . $county->id,
        ]);

        $county->update([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Megye sikeresen frissítve!');
    }

    public function destroy($id)
    {
        $county = County::findOrFail($id);
        $county->delete();

        return redirect()->route('counties.index')->with('success', 'Megye sikeresen törölve!');
    }
    public function sendPdfEmail(Request $request, County $county)
    {
        $initial = $request->query('initial');
        
        $query = $county->places();
        if ($initial) {
            $query->whereRaw('UPPER(LEFT(name, 1)) = ?', [$initial]);
        }
        
        $data = [
            'county' => $county,
            'places' => $query->orderBy('name')->get()
        ];
        $pdf = Pdf::loadView('pdf.countypdf', $data)->setPaper('a4', 'portrait');

        Mail::to(Auth::user()->email)->send(new CountyReportMail($county, $pdf->output()));

        return redirect()->back()->with('success', 'A PDF sikeresen elküldve az email címedre!');
    }
}