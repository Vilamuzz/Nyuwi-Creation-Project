<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionSearchRequest;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RegionController extends Controller
{
    private function isPlainJsonRequest(Request $request): bool
    {
        return !$request->header('X-Inertia') && ($request->wantsJson() || $request->ajax());
    }

    public function provinces(Request $request)
    {
        $data = Province::orderBy('name')->get();
        if ($this->isPlainJsonRequest($request)) {
            return response()->json($data);
        }
        return back()->with('regionData', $data);
    }

    public function regencies(Request $request, $provinceId)
    {
        $data = Province::findOrFail($provinceId)->regencies()->orderBy('name')->get();
        if ($this->isPlainJsonRequest($request)) {
            return response()->json($data);
        }
        return back()->with('regionData', $data);
    }

    public function districts(Request $request, $regencyId)
    {
        $data = Regency::findOrFail($regencyId)->districts()->orderBy('name')->get();
        if ($this->isPlainJsonRequest($request)) {
            return response()->json($data);
        }
        return back()->with('regionData', $data);
    }

    public function villages(Request $request, $districtId)
    {
        $data = District::findOrFail($districtId)->villages()->orderBy('name')->get();
        if ($this->isPlainJsonRequest($request)) {
            return response()->json($data);
        }
        return back()->with('regionData', $data);
    }

    public function cityByName(string $cityName)
    {
        $cleanCityName = preg_replace('/^(KABUPATEN\s+|KOTA\s+)/i', '', $cityName);
        $regency = Regency::where(function ($query) use ($cityName, $cleanCityName) {
            $query->where('name', 'LIKE', "%{$cityName}%")
                ->orWhere('name', 'LIKE', "%{$cleanCityName}%")
                ->orWhere('name', 'LIKE', "KABUPATEN {$cleanCityName}")
                ->orWhere('name', 'LIKE', "KOTA {$cleanCityName}");
        })->with('province')->firstOrFail();

        return back()->with('regionData', [
            'city' => $regency,
            'province' => $regency->province,
            'all_cities_in_province' => $regency->province->regencies,
        ]);
    }

    public function search(RegionSearchRequest $request)
    {
        $query = $request->validated('q');
        $type = $request->validated('type', 'all');
        $results = [];

        if ($type === 'province' || $type === 'all') {
            $results['provinces'] = Province::where('name', 'LIKE', "%{$query}%")->get();
        }
        if ($type === 'regency' || $type === 'all') {
            $results['regencies'] = Regency::where('name', 'LIKE', "%{$query}%")->get();
        }
        if ($type === 'district' || $type === 'all') {
            $results['districts'] = District::where('name', 'LIKE', "%{$query}%")->get();
        }
        if ($type === 'village' || $type === 'all') {
            $results['villages'] = Village::where('name', 'LIKE', "%{$query}%")->get();
        }

        return back()->with('regionData', $results);
    }
}
