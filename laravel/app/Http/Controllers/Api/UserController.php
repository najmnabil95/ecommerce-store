<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select('users.id', 'users.name', 'users.email', 'users.phone', 'users.address', 'users.role', 'users.created_at')
            ->leftJoin('orders', function ($join) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->where('orders.status', '!=', 'cancelled');
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone', 'users.address', 'users.role', 'users.created_at')
            ->selectRaw('COUNT(orders.id) as orders_count, COALESCE(SUM(orders.total), 0) as total_spent')
            ->orderBy('users.id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,customer'
        ]);

        $user = User::findOrFail($id);
        if ($user->email === 'admin@elite.com' && $request->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تغيير رتبة المشرف الرئيسي'
            ], 400);
        }

        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث صلاحية المستخدم بنجاح',
            'data' => $user
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'nullable|in:admin,customer',
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '',
            'address' => $validated['address'] ?? '',
            'role' => $validated['role'] ?? 'customer',
            'password' => bcrypt($validated['password'] ?? '12345678'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'تم إضافة العميل/المستخدم بنجاح',
        ], 201);
    }

    public function userOrders($id)
    {
        $user = User::findOrFail($id);
        $orders = \App\Models\Order::where('user_id', $user->id)
            ->orWhere('customer_email', $user->email)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($o) {
                $o->items = is_string($o->items_json) ? json_decode($o->items_json, true) : $o->items_json;
                return $o;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'orders' => $orders,
            ],
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->email === 'admin@elite.com') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف حساب المشرف الرئيسي'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المستخدم بنجاح'
        ]);
    }
}
