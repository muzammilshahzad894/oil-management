<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        $request->validate([
            'show_purchase_price' => 'nullable|in:0,1',
        ]);
        Setting::set('show_purchase_price', $request->has('show_purchase_price') ? '1' : '0');
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings saved successfully.');
    }
}
