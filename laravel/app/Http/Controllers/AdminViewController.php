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
    public function dashboard()
    {
        $productsCount = Product::count();
        $ordersCount = Order::count();
        $reviewsCount = Review::count();
        $couponsCount = Coupon::count();
        $usersCount = User::count();
        
        $totalSales = Order::whereIn('status', ['delivered', 'processing', 'shipped'])->sum('total');
        
        $recentOrders = Order::orderBy('id', 'desc')->take(8)->get();
        $lowStockProducts = Product::where('stock', '<=', 5)->get();
        $recentReviews = Review::with('product')->orderBy('id', 'desc')->take(6)->get();
        $categories = Category::all();
        $products = Product::with('category')->orderBy('id', 'desc')->get();
        $coupons = Coupon::orderBy('id', 'desc')->get();

        try {
            $versionRow = DB::select('SELECT VERSION() as v');
            $dbVersion = $versionRow[0]->v ?? 'MariaDB';
        } catch (\Exception $e) {
            $dbVersion = 'MariaDB 10.4+';
        }

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
            'dbVersion'
        ));
    }

    public function products()
    {
        $products = Product::with('category')->orderBy('id', 'desc')->paginate(15);
        $categories = Category::all();
        return view('admin.products', compact('products', 'categories'));
    }

    public function orders()
    {
        $orders = Order::orderBy('id', 'desc')->paginate(15);
        return view('admin.orders', compact('orders'));
    }

    public function reviews()
    {
        $reviews = Review::with('product')->orderBy('id', 'desc')->paginate(15);
        return view('admin.reviews', compact('reviews'));
    }

    public function audit()
    {
        return view('admin.audit');
    }

    public function database()
    {
        try {
            $tables = DB::select('SHOW TABLE STATUS');
        } catch (\Exception $e) {
            $tables = [];
        }
        return view('admin.database', compact('tables'));
    }
}
