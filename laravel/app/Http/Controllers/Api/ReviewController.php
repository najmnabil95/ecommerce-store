<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with('product:id,name_ar,name_en,image_url')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $productId = $review->product_id;
        $review->delete();

        // Recalculate product rating
        $product = Product::find($productId);
        if ($product) {
            $avgRating = Review::where('product_id', $productId)->avg('rating') ?: 5.0;
            $count = Review::where('product_id', $productId)->count();
            $product->rating = round($avgRating, 1);
            $product->reviews_count = $count;
            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقييم بنجاح وتحديث إحصائيات المنتج'
        ]);
    }
}
