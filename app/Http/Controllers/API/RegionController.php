<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;

class RegionController extends Controller
{
    /**
     * Get all provinces
     */
    public function getProvinces()
    {
        try {
            $provinces = Province::all();
            return response()->json([
                'success' => true,
                'data' => $provinces
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch provinces'
            ], 500);
        }
    }

    /**
     * Get regencies/cities by province ID
     */
    public function getRegencies($provinceId)
    {
        try {
            $province = Province::findOrFail($provinceId);
            $regencies = $province->regencies;

            return response()->json([
                'success' => true,
                'data' => $regencies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch regencies'
            ], 500);
        }
    }

    /**
     * Get city by name and return with province information
     */
    public function getCityByName($cityName)
    {
        try {
            // Clean the city name (remove KABUPATEN/KOTA prefixes)
            $cleanCityName = preg_replace('/^(KABUPATEN\s+|KOTA\s+)/i', '', $cityName);

            // Search for the city with different possible formats
            $regency = Regency::where(function ($query) use ($cityName, $cleanCityName) {
                $query->where('name', 'LIKE', '%' . $cityName . '%')
                    ->orWhere('name', 'LIKE', '%' . $cleanCityName . '%')
                    ->orWhere('name', 'LIKE', 'KABUPATEN ' . $cleanCityName)
                    ->orWhere('name', 'LIKE', 'KOTA ' . $cleanCityName);
            })->with('province')->first();

            if (!$regency) {
                return response()->json([
                    'success' => false,
                    'message' => 'City not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'city' => $regency,
                    'province' => $regency->province,
                    'all_cities_in_province' => $regency->province->regencies
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch city information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get districts by regency/city ID
     */
    public function getDistricts($regencyId)
    {
        try {
            $regency = Regency::findOrFail($regencyId);
            $districts = $regency->districts;

            return response()->json([
                'success' => true,
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch districts'
            ], 500);
        }
    }

    /**
     * Get villages by district ID
     */
    public function getVillages($districtId)
    {
        try {
            $district = District::findOrFail($districtId);
            $villages = $district->villages;

            return response()->json([
                'success' => true,
                'data' => $villages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch villages'
            ], 500);
        }
    }

    /**
     * Search regions by name
     */
    public function searchRegions(Request $request)
    {
        try {
            $query = $request->get('q');
            $type = $request->get('type', 'all'); // province, regency, district, village, all

            $results = [];

            if ($type === 'province' || $type === 'all') {
                $provinces = Province::where('name', 'LIKE', "%{$query}%")->get();
                $results['provinces'] = $provinces;
            }

            if ($type === 'regency' || $type === 'all') {
                $regencies = Regency::where('name', 'LIKE', "%{$query}%")->get();
                $results['regencies'] = $regencies;
            }

            if ($type === 'district' || $type === 'all') {
                $districts = District::where('name', 'LIKE', "%{$query}%")->get();
                $results['districts'] = $districts;
            }

            if ($type === 'village' || $type === 'all') {
                $villages = Village::where('name', 'LIKE', "%{$query}%")->get();
                $results['villages'] = $villages;
            }

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search regions'
            ], 500);
        }
    }
}
