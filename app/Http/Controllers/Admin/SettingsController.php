<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Setting::all()->keyBy('key');

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'commission_rate'        => 'required|numeric|min:0|max:1',
            'order_timeout_hours'    => 'required|integer|min:1|max:168',
            'auto_approve_days'      => 'required|integer|min:1|max:30',
            'max_revisions'          => 'required|integer|min:0|max:10',
            'max_portfolio_size_mb'  => 'required|integer|min:1|max:100',
            'max_attachment_size_mb' => 'required|integer|min:1|max:100',
        ]);

        foreach ($validated as $key => $value) {
            $type = match ($key) {
                'commission_rate' => 'decimal',
                default           => 'integer',
            };

            Setting::set($key, $value, $type);
        }

        // Clear all setting caches
        Cache::flush();

        return back()->with('success', 'Settings updated successfully.');
    }
}
