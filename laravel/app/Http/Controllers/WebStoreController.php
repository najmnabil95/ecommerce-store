<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;

class WebStoreController extends Controller
{
    public function home()
    {
        $categories = Category::withCount('products')->get();
        $products = Product::with(['category', 'reviews'])->orderBy('id', 'desc')->take(12)->get();
        $featuredProducts = Product::where('badge', '!=', '')->take(4)->get();
        $settings = Setting::pluck('value', 'key')->all();

        return view('home', compact('categories', 'products', 'featuredProducts', 'settings'));
    }

    public function products(Request $request)
    {
        $categories = Category::all();
        $query = Product::with(['category', 'reviews'])->orderBy('id', 'desc');

        if ($request->filled('category') && $request->category !== 'all') {
            $cat = $request->category;
            $query->whereHas('category', function ($q) use ($cat) {
                $q->where('slug', $cat)->orWhere('id', $cat);
            });
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', $term)
                  ->orWhere('name_en', 'like', $term)
                  ->orWhere('description_ar', 'like', $term);
            });
        }

        if ($request->filled('size') && $request->size !== 'all') {
            $query->whereJsonContains('sizes', $request->size);
        }

        if ($request->filled('fit') && $request->fit !== 'all') {
            $query->where('fit_type', $request->fit);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->filled('in_stock')) {
            $query->where('stock', '>', 0);
        }

        if ($request->sort === 'price-low') {
            $query->reorder('price', 'asc');
        } elseif ($request->sort === 'price-high') {
            $query->reorder('price', 'desc');
        } elseif ($request->sort === 'rating') {
            $query->reorder('rating', 'desc');
        }

        $products = $query->get();

        return view('products.index', compact('categories', 'products'));
    }

    public function showProduct($id)
    {
        $product = Product::with(['category', 'reviews'])->findOrFail($id);
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function category($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $categories = Category::all();
        $products = Product::where('category_id', $category->id)->with(['category', 'reviews'])->get();

        return view('products.index', compact('categories', 'products', 'category'));
    }

    public function compare(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $products = !empty($ids) ? Product::whereIn('id', $ids)->with('category')->get() : collect();

        return view('compare', compact('products'));
    }

    public function cart()
    {
        return view('cart');
    }

    public function checkout()
    {
        return view('checkout');
    }

    public function track(Request $request)
    {
        $orderId = $request->query('order');
        return view('track', compact('orderId'));
    }
}
