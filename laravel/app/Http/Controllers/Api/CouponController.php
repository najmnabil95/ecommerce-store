<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'discount_percent' => 'required|numeric|min:1|max:100',
            'min_order' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;
        $validated['min_order'] = $validated['min_order'] ?? 0;
        $validated['max_discount'] = $validated['max_discount'] ?? 0;
        $validated['uses_count'] = 0;

        $coupon = Coupon::create($validated);

        return response()->json([
            'success' => true,
            'data' => $coupon,
            'message' => 'تم إنشاء كود الخصم بنجاح'
        ], 201);
    }

    public function toggle($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return response()->json([
            'success' => true,
            'data' => $coupon,
            'message' => $coupon->is_active ? 'تم تفعيل الكوبون بنجاح' : 'تم تعطيل الكوبون بنجاح'
        ]);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الكوبون بنجاح'
        ]);
    }

    public function validateCoupon(Request $request)
    {
        $code = strtoupper(trim($request->input('code', '')));
        $subtotal = floatval($request->input('subtotal', 0));

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال كود الخصم'
            ], 400);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم غير موجود'
            ], 400);
        }

        if (!$coupon->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم معطل أو منتهي الصلاحية'
            ], 400);
        }

        if ($coupon->min_order > 0 && $subtotal < $coupon->min_order) {
            return response()->json([
                'success' => false,
                'message' => "الحد الأدنى لاستخدام هذا الكود هو {$coupon->min_order} ر.س"
            ], 400);
        }

        $rawDiscount = ($subtotal * $coupon->discount_percent) / 100;
        $discount = ($coupon->max_discount > 0 && $rawDiscount > $coupon->max_discount)
            ? $coupon->max_discount
            : $rawDiscount;

        return response()->json([
            'success' => true,
            'data' => [
                'valid' => true,
                'code' => $coupon->code,
                'discount_percent' => $coupon->discount_percent,
                'discount' => round($discount, 2),
                'message' => "تم تطبيق كود الخصم ({$coupon->discount_percent}%) بنجاح!"
            ]
        ]);
    }
}
