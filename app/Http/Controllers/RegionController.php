<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionSearchRequest;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Inertia\Inertia;

class RegionController extends Controller
{
    public function provinces()
    {
        return back()->with('regionData', Province::all());
    }

    public function regencies($provinceId)
    {
        return back()->with('regionData', Province::findOrFail($provinceId)->regencies);
    }

    public function districts($regencyId)
    {
        return back()->with('regionData', Regency::findOrFail($regencyId)->districts);
    }

    public function villages($districtId)
    {
        return back()->with('regionData', District::findOrFail($districtId)->villages);
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
