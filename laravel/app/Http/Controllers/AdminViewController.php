<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminViewController extends Controller
{
    public function dashboard(Request $request, $tab = 'overview-tab')
    {
        $productsCount = Product::count();
        $ordersCount = Order::count();
        $reviewsCount = Review::count();
        $couponsCount = Coupon::count();
        $usersCount = User::count();
        
        $totalSales = Order::whereIn('status', ['delivered', 'processing', 'shipped'])->sum('total');
        
        $recentOrders = Order::orderBy('id', 'desc')->take(15)->get();
        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $recentReviews = Review::with('product')->orderBy('id', 'desc')->take(15)->get();
        $categories = Category::all();
        $products = Product::with('category')->orderBy('id', 'desc')->get();
        $coupons = Coupon::orderBy('id', 'desc')->get();

        try {
            $versionRow = DB::select('SELECT VERSION() as v');
            $dbVersion = $versionRow[0]->v ?? 'MariaDB';
        } catch (\Exception $e) {
            $dbVersion = 'MariaDB 10.4+';
        }

        $activeTab = $tab;

        return view('admin.dashboard', compact(
            'productsCount',
            'ordersCount',
            'reviewsCount',
            'couponsCount',
            'usersCount',
            'totalSales',
            'recentOrders',
            'lowStockProducts',
            'recentReviews',
            'categories',
            'products',
            'coupons',
            'dbVersion',
            'activeTab'
        ));
    }

    public function products(Request $request)
    {
        return $this->dashboard($request, 'products-tab');
    }

    public function orders(Request $request)
    {
        return $this->dashboard($request, 'orders-tab');
    }

    public function reviews(Request $request)
    {
        return $this->dashboard($request, 'reviews-tab');
    }

    public function audit(Request $request)
    {
        return $this->dashboard($request, 'audit-tab');
    }

    public function database(Request $request)
    {
        return $this->dashboard($request, 'database-tab');
    }
}
