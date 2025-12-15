<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubcategoryRequest;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index()
    {
        $data = DB::table('sub_categories', 'sc')
            ->join('categories', 'sc.category_id', '=', 'categories.id')
            ->select('sc.*', 'categories.name as category_name')
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
        //
    }

    // -------------------------------

    public function toggleStatus() {}
}
