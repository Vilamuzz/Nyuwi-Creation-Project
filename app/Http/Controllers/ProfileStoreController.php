<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfileUpdateRequest;
use App\Models\ProfileStore;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileStoreController extends Controller
{
    public function __construct(private ShippingService $shipping) {}

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

        // RajaOngkir has no reverse lookup, so the admin form resolves the
        // selected city/district client-side and hydrates the rest on change.
        return Inertia::render('Admin/ProfileStore', [
            'profile' => $profile,
            'supportedCouriers' => $supportedCouriers,
            'provinces' => $this->shipping->provinces(),
        ]);
    }

    public function update(StoreProfileUpdateRequest $request)
    {
        $profile = ProfileStore::first() ?? new ProfileStore;

        $validatedData = $request->validated();

        $updateData = [
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'embedded_map' => $validatedData['embedded_map'] ?? null,
            'district_id' => $validatedData['district_id'] ?? null,
            'district_name' => $validatedData['district_name'] ?? null,
            'shipping_couriers' => $validatedData['shipping_couriers'] ?? ['jne', 'pos', 'tiki'],
            'phone' => $validatedData['phone'],
            'instagram' => $validatedData['instagram'] ?? null,
            'facebook' => $validatedData['facebook'] ?? null,
            'tiktok' => $validatedData['tiktok'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            if ($profile->logo) {
                $oldPath = str_replace('/storage/', '', $profile->logo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $storedPath = $request->file('logo')->store('logo', 'public');
            $updateData['logo'] = '/storage/' . $storedPath;
        }

        if (! $profile->exists) {
            $updateData['logo'] = $updateData['logo'] ?? '';
            $profile->fill($updateData)->save();
        } else {
            $profile->update($updateData);
        }

        Cache::forget('store_profile');

        return back()->with('success', 'Profil toko berhasil diperbarui!');
    }
}
