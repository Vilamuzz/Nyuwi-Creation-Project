<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\ProfileStore;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Artisan;
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
            'admin_registration_code' => 'nullable|string|min:6|max:50',
        ];

        // Only require the logo if a new one is uploaded
        if ($request->hasFile('logo')) {
            $rules['logo'] = 'image|mimes:jpeg,png,jpg,svg|max:2048';
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

        // Update admin registration code in .env if provided
        if (!empty($validatedData['admin_registration_code'])) {
            $this->updateEnvFile('ADMIN_REGISTRATION_CODE', $validatedData['admin_registration_code']);
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

    /**
     * Update environment file
     */
    private function updateEnvFile($key, $value)
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        // Escape special characters for regex
        $escapedKey = preg_quote($key, '/');

        // Check if key exists
        if (preg_match("/^{$escapedKey}=.*/m", $envContent)) {
            // Update existing key
            $envContent = preg_replace("/^{$escapedKey}=.*/m", "{$key}={$value}", $envContent);
        } else {
            // Add new key
            $envContent .= "\n{$key}={$value}";
        }

        file_put_contents($envFile, $envContent);

        // Clear config cache to reflect changes
        Artisan::call('config:clear');
    }
}
