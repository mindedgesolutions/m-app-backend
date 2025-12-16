<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubcategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index()
    {
        $search = request()->query('search');

        $data = DB::table('sub_categories', 'sc')
            ->join('categories', 'sc.category_id', '=', 'categories.id')
            ->select('sc.*', 'categories.name as category_name')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('sc.name', 'ilike', "%{$search}%")
                        ->orWhere('categories.name', 'ilike', "%{$search}%");
                });
            })
            ->orderBy('categories.name')
            ->orderBy('sc.name')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------

    public function store(SubcategoryRequest $request)
    {
        $iconLib = null;
        $iconName = null;
        if ($request->icon) {
            $split = explode('|', $request->icon);
            $iconName = trim($split[1]);
            $iconLib = trim($split[0]);
        }
        SubCategory::create([
            'category_id' => $request->categoryId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'description' => $request->description ? trim($request->description) : null,
            'icon' => $iconName,
            'library' => $iconLib,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_CREATED);
    }

    // -------------------------------

    public function update(Request $request, string $id)
    {
        $iconLib = null;
        $iconName = null;
        if ($request->icon) {
            $split = explode('|', $request->icon);
            $iconName = trim($split[1]);
            $iconLib = trim($split[0]);
        }
        SubCategory::whereId($id)->update([
            'category_id' => $request->categoryId,
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'description' => $request->description ? trim($request->description) : null,
            'icon' => $iconName,
            'library' => $iconLib,
        ]);
        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function destroy(string $id)
    {
        if (Product::where('sub_category_id', $id)->count() > 0) {
            return response()->json(['message' => 'Sub-category has associated products and cannot be deleted'], Response::HTTP_BAD_REQUEST);
        }
        // Check orders - if exists, don't allow delete
        SubCategory::whereId($id)->delete();

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }

    // -------------------------------

    public function toggleStatus(Request $request, $id)
    {
        if (Product::where('sub_category_id', $id)->count() > 0) {
            return response()->json(['message' => 'Sub-category has associated products and cannot be deactivated'], Response::HTTP_BAD_REQUEST);
        }
        // Check orders - if exists, don't allow deactivate

        if ($request->is_active) {
            $category = SubCategory::whereId($id)->get()->value('category_id');
            $status = $request->is_active ? true : false;
            Category::whereId($category)->update(['is_active' => $status]);
        }

        SubCategory::whereId($id)->update(['is_active' => $request->is_active]);

        return response()->json(['message' => 'success'], Response::HTTP_OK);
    }
}
