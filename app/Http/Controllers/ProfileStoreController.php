<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ProfileStore;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileStoreController extends Controller
{
    public function index() {}

    public function update(Request $request, $name)
    {
        $profile = ProfileStore::where('name', $name)->firstOrFail();

        // Define validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'instagram' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
        ];

        // Only require the logo if a new one is uploaded
        if ($request->hasFile('logo')) {
            $rules['logo'] = 'image|mimes:jpeg,png,jpg,svg|max:2048';
        }

        // Only require the QRIS if a new one is uploaded
        if ($request->hasFile('qris')) {
            $rules['qris'] = 'image|mimes:jpeg,png,jpg,svg|max:2048';
        }

        $validatedData = $request->validate($rules);

        // Prepare update data
        $updateData = [
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'phone' => $validatedData['phone'],
            'instagram' => $validatedData['instagram'] ?? null,
            'facebook' => $validatedData['facebook'] ?? null,
            'tiktok' => $validatedData['tiktok'] ?? null,
        ];

        // Process and store the logo if one was uploaded
        if ($request->hasFile('logo')) {
            // Delete the old logo if it exists
            if ($profile->logo && Storage::disk('public')->exists($profile->logo)) {
                Storage::disk('public')->delete($profile->logo);
            }

            // Store the new logo
            $logoPath = $request->file('logo')->store('logo', 'public');
            $updateData['logo'] = $logoPath;

            // Create favicon using Intervention Image v3
            $manager = new ImageManager(new Driver());
            $image = $manager->read(storage_path('app/public/' . $logoPath));

            // Resize to 32x32 for favicon
            $favicon = $image->resize(32, 32);

            // Save favicon to public directory
            $faviconPath = public_path('favicon.ico');
            $favicon->save($faviconPath);
        }

        // Process and store the QRIS image if one was uploaded
        if ($request->hasFile('qris')) {
            // Delete the old QRIS image if it exists
            if ($profile->qris && Storage::disk('public')->exists($profile->qris)) {
                Storage::disk('public')->delete($profile->qris);
            }

            // Store the new QRIS image
            $qrisPath = $request->file('qris')->store('qris', 'public');
            $updateData['qris'] = $qrisPath;
        }

        $profile->update($updateData);

        return back()->with('success', 'Store profile updated successfully!');
    }

    public function edit($name)
    {
        $profile = ProfileStore::where('name', $name)->firstOrFail();
        return Inertia::render('Admin/ProfileStore/Edit', [
            'profile' => $profile
        ]);
    }
}
