<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\CostSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CostSettingController extends Controller
{
    public function index()
    {
        $settings = CostSetting::all();
        dd($settings);
        return view('admin.cost-settings.index', compact('settings'));
    }

    public function edit(CostSetting $costSetting)
    {
        return view('admin.cost-settings.edit', compact('costSetting'));
    }

    public function update(Request $request, CostSetting $costSetting)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $costSetting->update(['value' => $request->value]);

        return redirect()->route('admin.cost-settings.index')
            ->with('success', 'Cost setting updated successfully.');
    }

    public function updateMultiple(Request $request)
    {
        $settings = $request->except('_token');

        foreach ($settings as $key => $value) {
            $setting = CostSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        return redirect()->route('admin.cost-settings.index')
            ->with('success', 'Cost settings updated successfully.');
    }
}
