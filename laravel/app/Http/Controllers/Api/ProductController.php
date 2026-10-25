<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
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
            $sz = $request->size;
            $query->whereJsonContains('sizes', $sz);
        }

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('fit') && $request->fit !== 'all') {
            $query->where('fit_type', $request->fit);
        }

        if ($request->sort === 'price-low') {
            $query->reorder('price', 'asc');
        } elseif ($request->sort === 'price-high') {
            $query->reorder('price', 'desc');
        } elseif ($request->sort === 'rating') {
            $query->reorder('rating', 'desc');
        }

        $products = $query->get()->map(function ($p) {
            $p->category_name = $p->category?->name_ar;
            $p->category_slug = $p->category?->slug;
            $p->gallery = $this->getGalleryForProduct($p);
            return $p;
        });

        return response()->json([
            'success' => true,
            'count' => $products->count(),
            'data' => $products,
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'reviews'])->findOrFail($id);
        $product->category_name = $product->category?->name_ar;
        $product->category_slug = $product->category?->slug;
        $product->gallery = $this->getGalleryForProduct($product);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'badge' => 'nullable|string',
            'image_url' => 'required|string',
            'description_ar' => 'nullable|string',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'material' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'fit_type' => 'nullable|string',
            'gender' => 'nullable|string',
        ]);

        if (isset($validated['sizes']) && is_string($validated['sizes'])) {
            $validated['sizes'] = json_decode($validated['sizes'], true) ?: explode(',', $validated['sizes']);
        }
        if (isset($validated['colors']) && is_string($validated['colors'])) {
            $validated['colors'] = json_decode($validated['colors'], true) ?: $validated['colors'];
        }

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'تمت إضافة المنتج بنجاح',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name_ar' => 'sometimes|required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'badge' => 'nullable|string',
            'image_url' => 'sometimes|required|string',
            'description_ar' => 'nullable|string',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'material' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'fit_type' => 'nullable|string',
            'gender' => 'nullable|string',
        ]);

        if (isset($validated['sizes']) && is_string($validated['sizes'])) {
            $validated['sizes'] = json_decode($validated['sizes'], true) ?: explode(',', $validated['sizes']);
        }
        if (isset($validated['colors']) && is_string($validated['colors'])) {
            $validated['colors'] = json_decode($validated['colors'], true) ?: $validated['colors'];
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'تم تحديث بيانات المنتج بنجاح',
        ]);
    }

    public function quickUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = [];
        if ($request->has('delta')) {
            $data['stock'] = max(0, (int) $product->stock + (int) $request->input('delta'));
        } elseif ($request->has('stock')) {
            $data['stock'] = max(0, (int) $request->input('stock'));
        }
        if ($request->has('price')) {
            $data['price'] = max(0, (float) $request->input('price'));
        }
        if ($request->has('badge')) {
            $data['badge'] = $request->input('badge');
        }
        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'تم تحديث المخزون والبيانات بنجاح',
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المنتج بنجاح',
        ]);
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('product_ids') ?? $request->input('ids') ?? [];

        if (empty($action) || empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تحديد المنتجات والإجراء المطلوب'
            ], 422);
        }

        if ($action === 'delete') {
            Product::whereIn('id', $ids)->delete();
            return response()->json(['success' => true, 'message' => "تم حذف " . count($ids) . " منتج بنجاح"]);
        }

        if ($action === 'update_stock') {
            $mode = $request->input('mode', 'set');
            $val = (int) ($request->input('value') ?? $request->input('stock') ?? 0);

            $products = Product::whereIn('id', $ids)->get();
            foreach ($products as $p) {
                if ($mode === 'add') {
                    $p->update(['stock' => $p->stock + $val]);
                } elseif ($mode === 'subtract') {
                    $p->update(['stock' => max(0, $p->stock - $val)]);
                } else {
                    $p->update(['stock' => max(0, $val)]);
                }
            }
            return response()->json(['success' => true, 'message' => "تم تحديث مخزون المنتجات المحددة (" . count($ids) . " منتج) بنجاح"]);
        }

        if ($action === 'adjust_price') {
            $adjType = $request->input('adjustment_type', 'increase');
            $percent = (float) ($request->input('percentage') ?? $request->input('value') ?? 0);
            if ($adjType === 'discount') {
                $percent = -abs($percent);
            }

            $products = Product::whereIn('id', $ids)->get();
            foreach ($products as $p) {
                $newPrice = round($p->price * (1 + ($percent / 100)), 2);
                $p->update(['price' => max(1, $newPrice)]);
            }
            $sign = $percent >= 0 ? "+{$percent}%" : "{$percent}%";
            return response()->json(['success' => true, 'message' => "تم تعديل أسعار المنتجات المحددة بنسبة {$sign} بنجاح"]);
        }

        if ($action === 'change_category') {
            $catId = (int) ($request->input('category_id') ?? $request->input('value') ?? 1);
            Product::whereIn('id', $ids)->update(['category_id' => $catId]);
            return response()->json(['success' => true, 'message' => 'تم نقل المنتجات المحددة إلى القسم الجديد بنجاح']);
        }

        return response()->json(['success' => false, 'message' => 'إجراء غير مدعوم'], 400);
    }

    public function addReview(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'author' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'author' => $validated['author'] ?? 'عميل المتجر',
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'date' => now()->format('Y-m-d'),
        ]);

        // Recalculate average rating & reviews_count
        $avg = Review::where('product_id', $product->id)->avg('rating');
        $cnt = Review::where('product_id', $product->id)->count();

        $product->update([
            'rating' => round($avg, 1),
            'reviews_count' => $cnt,
        ]);

        return response()->json([
            'success' => true,
            'data' => $product->load('reviews'),
            'message' => 'تمت إضافة التقييم بنجاح',
        ], 201);
    }

    private function getGalleryForProduct($prod)
    {
        $catGallery = [
            1 => [
                'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80',
            ],
            2 => [
                'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
            ],
            3 => [
                'https://images.unsplash.com/photo-1547887537-6158d64c35b3?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1583445013765-46c20c4a6772?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1615397349754-cfa2066a298e?w=600&auto=format&fit=crop&q=80',
            ],
            4 => [
                'https://images.unsplash.com/photo-1524805444758-089113d48a6d?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1611085583191-a3b181a88401?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=600&auto=format&fit=crop&q=80',
            ],
            5 => [
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?w=600&auto=format&fit=crop&q=80',
                'https://images.unsplash.com/photo-1517705008128-361805f42e86?w=600&auto=format&fit=crop&q=80',
            ],
        ];

        $extra = $catGallery[$prod->category_id] ?? [];
        return array_values(array_unique(array_merge([$prod->image_url], $extra)));
    }
}
