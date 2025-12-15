<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = Category::when($search, function ($query) use ($search) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
        })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------

    public function store(CategoryRequest $request)
    {
        $iconLib = null;
        $iconName = null;
        if ($request->icon) {
            $split = explode('|', $request->icon);
            $iconName = trim($split[1]);
            $iconLib = trim($split[0]);
        }
        Category::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'description' => $request->description ? trim($request->description) : null,
            'icon' => $iconName,
            'library' => $iconLib,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(CategoryRequest $request, $id)
    {
        $iconLib = null;
        $iconName = null;
        Log::info('Icon received: ' . $request->icon);
        if ($request->icon) {
            $split = explode('|', $request->icon);
            $iconName = trim($split[1]);
            $iconLib = trim($split[0]);
        }
        Category::whereId($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'description' => $request->description ? trim($request->description) : null,
            'icon' => $iconName,
            'library' => $iconLib,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function destroy($id)
    {
        // Check orders - if exists, don't allow delete
        // Delete all listings from products table
        $subs = SubCategory::where('category_id', $id)->get();
        foreach ($subs as $sub) {
            $sub->delete();
        }

        Category::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function toggleStatus(Request $request, $id)
    {
        $subs = SubCategory::where('category_id', $id)->get();
        foreach ($subs as $sub) {
            $sub->update(['is_active' => $request->is_active]);
        }
        // Deactivate all listings from products table

        Category::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
