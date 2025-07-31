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
     * Get complete region data (province -> regency -> district -> village)
     */
    public function getCompleteRegion($provinceId = null, $regencyId = null, $districtId = null)
    {
        try {
            $data = [];

            if ($provinceId) {
                $province = Province::with('regencies')->findOrFail($provinceId);
                $data['province'] = $province;

                if ($regencyId) {
                    $regency = Regency::with('districts')->findOrFail($regencyId);
                    $data['regency'] = $regency;

                    if ($districtId) {
                        $district = District::with('villages')->findOrFail($districtId);
                        $data['district'] = $district;
                    }
                }
            } else {
                $data['provinces'] = Province::all();
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch region data'
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->getProvinces();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is not available for creating regions'
        ], 405);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->getCompleteRegion($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is not available for updating regions'
        ], 405);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is not available for deleting regions'
        ], 405);
    }
}
