<?php

namespace App\Http\Controllers;

use App\Services\ShippingService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private ShippingService $ro) {}

    public function provinces()
    {
        return response()->json($this->ro->provinces());
    }

    public function cities($provinceId)
    {
        return response()->json($this->ro->cities($provinceId));
    }

    public function districts($cityId)
    {
        return response()->json($this->ro->districts($cityId));
    }

    public function cost(Request $r)
    {
        $data = $r->validate([
            'district_id' => 'required|string',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
        ]);

        return response()->json($this->ro->cost((int) $data['district_id'], $data['weight'], $data['courier']));
    }
}
