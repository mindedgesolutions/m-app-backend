<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $data = DB::table('products', 'pr')
            ->join('categories as cat', 'pr.category_id', '=', 'cat.id')
            ->join('sub_categories as sub', 'pr.sub_category_id', '=', 'sub.id')
            ->join('product_images as pi', 'pr.id', '=', 'pi.product_id')
            ->select(
                'pr.*',
                'cat.id as category_id',
                'cat.name as category_name',
                'sub.id as sub_category_id',
                'sub.name as sub_category_name',
                'pi.*'
            )
            ->orderBy('cat.name', 'asc')
            ->orderBy('sub.name', 'asc')
            ->orderBy('pr.name', 'asc')
            ->paginate(10);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    // -------------------------------

    public function store(Request $request)
    {
        //
    }

    // -------------------------------

    public function show(string $id)
    {
        //
    }

    // -------------------------------

    public function update(Request $request, string $id)
    {
        //
    }

    // -------------------------------

    public function destroy(string $id)
    {
        //
    }

    // -------------------------------

    public function toggleStatus(Request $request, $id)
    {
        //
    }
}
