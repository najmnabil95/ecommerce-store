<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private function getDefaults(): array
    {
        return [
            'store_name_ar' => 'متجر النخبة الفاخر',
            'store_name_en' => 'Elite Luxury Store',
            'currency' => '$',
            'shipping_cost' => '15.00',
            'free_shipping_threshold' => '150.00',
            'tax_percent' => '15',
            'contact_email' => 'support@elite-store.com',
            'contact_phone' => '+966 50 123 4567',
            'announcement_text' => ''
        ];
    }

    public function index()
    {
        $settings = $this->getDefaults();
        $stored = Setting::all()->pluck('value', 'key')->toArray();

        foreach ($stored as $k => $v) {
            $settings[$k] = $v;
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_null($value) ? '' : (string)$value]
            );
        }

        $settings = $this->getDefaults();
        $stored = Setting::all()->pluck('value', 'key')->toArray();

        foreach ($stored as $k => $v) {
            $settings[$k] = $v;
        }

        return response()->json([
            'success' => true,
            'data' => $settings,
            'message' => 'تم حفظ إعدادات المتجر بنجاح'
        ]);
    }
}
