<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ProfileStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProfileUpdateRequest;

class ProfileStoreController extends Controller
{
    public function index()
    {
        $profile = ProfileStore::first();

        return Inertia::render('Admin/ProfileStore', [
            'profile' => $profile,
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
