<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ProfileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProfileUpdateRequest;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;

class ProfileStoreController extends Controller
{
    public function index()
    {
        $profile = ProfileStore::first();

        $supportedCouriers = [
            'jne' => 'JNE Express',
            'pos' => 'POS Indonesia',
            'tiki' => 'TIKI',
            'sicepat' => 'SiCepat Ekspres',
            'jnt' => 'J&T Express',
        ];

        $provinces = Province::orderBy('name')->get(['id', 'name']);
        $currentProvinceId = null;
        $initialRegencies = [];
        $initialDistricts = [];

        $regency = null;
        if ($profile?->shipping_origin_city_id) {
            $regency = Regency::find($profile->shipping_origin_city_id);
        } elseif ($profile?->shipping_origin_district_id) {
            $district = District::find($profile->shipping_origin_district_id);
            $regency = $district?->regency;
        }

        if ($regency) {
            $currentProvinceId = (string) $regency->province_id;
            $initialRegencies = Regency::where('province_id', $regency->province_id)
                ->orderBy('name')
                ->get(['id', 'name']);
            $initialDistricts = District::where('regency_id', $regency->id)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return Inertia::render('Admin/ProfileStore', [
            'profile' => $profile,
            'supportedCouriers' => $supportedCouriers,
            'provinces' => $provinces,
            'currentProvinceId' => $currentProvinceId,
            'initialRegencies' => $initialRegencies,
            'initialDistricts' => $initialDistricts,
        ]);
    }

    public function update(StoreProfileUpdateRequest $request)
    {
        $profile = ProfileStore::first() ?? new ProfileStore();

        $validatedData = $request->validated();

        $updateData = [
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'shipping_origin_city_id' => $validatedData['shipping_origin_city_id'] ?? null,
            'shipping_origin_district_id' => $validatedData['shipping_origin_district_id'] ?? null,
            'shipping_couriers' => $validatedData['shipping_couriers'] ?? ['jne', 'pos', 'tiki'],
            'phone' => $validatedData['phone'],
            'instagram' => $validatedData['instagram'] ?? null,
            'facebook' => $validatedData['facebook'] ?? null,
            'tiktok' => $validatedData['tiktok'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            if ($profile->logo && Storage::disk('public')->exists($profile->logo)) {
                Storage::disk('public')->delete($profile->logo);
            }

            $updateData['logo'] = $request->file('logo')->store('logo', 'public');
        }

        if ($request->hasFile('qris')) {
            if ($profile->qris && Storage::disk('public')->exists($profile->qris)) {
                Storage::disk('public')->delete($profile->qris);
            }

            $updateData['qris'] = $request->file('qris')->store('qris', 'public');
        }

        if (!$profile->exists) {
            $updateData['logo'] = $updateData['logo'] ?? '';
            $updateData['qris'] = $updateData['qris'] ?? '';
            $profile->fill($updateData)->save();
        } else {
            $profile->update($updateData);
        }

        Cache::forget('store_profile');

        return back()->with('success', 'Profil toko berhasil diperbarui!');
    }
}
