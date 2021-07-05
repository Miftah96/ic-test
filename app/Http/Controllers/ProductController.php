<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query  = Product::query();

        if ($request->filled('name')){
            $query->where(function ($qr) use ($request){
                $qr->where('product_name', 'like', '%%'.$request->name.'%%');
            });
        }

        // Filter
        if ($request->filled('filter')) {
            if (isset($request->filter['is_active'])) {
                $query->filterStatus($request->filter['is_active']);
            }
        }

        // Sorting
        if ($request->filled('sortingid')) {
            $sortingid = $request->sortingid;
            $sorting = $request->sorting == 'descending' ? 'DESC' : 'ASC';
            $query->orderBy($sortingid, $sorting);
        }

        // Paginate
        if ($request->pagenumber) {
            $totalperpage = $request->totalperpage ? $request->totalperpage : 10;
            $query = $query->paginate($totalperpage, ['*'], 'paginatedata', $request->pagenumber);

            $data['total'] = $query->total();
            $data['totalperpage'] = $totalperpage;
            $data['countperpage'] = count($query);
            $data['currentpage'] = $query->currentPage();
            $data['lastpage'] = $query->lastPage();
        } else {
            $query = $query->get();
            $data['total'] = $query->count();
        }

        $data['data'] = new ProductCollection($query);

        return ResponseHelper::response($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $product    = Product::create($request->all());
            $data   = new ProductResource($product);

            DB::commit();
            return ResponseHelper::response($data);
        }
        catch (\Throwable $th){
            DB::rollBack();
            return ResponseHelper::response($th->getMessage(), 500);
        }

    }

    public function show($product)
    {
        $data = Product::where('product_id', $product)->get();
        return ResponseHelper::response($data);
    }

    public function update(Request $request,$product)
    {
        DB::beginTransaction();

        try {
            $product = Product::where('product_id', $product)->get();
            $product->product_id = $request->product_id;
            $product->product_name = $request->product_name;
            $product->price = $request->price;
            $product->update();

            DB::commit();
            return ResponseHelper::response($product);
        } catch (\Throwable $th) {
            DB::rollback();

            return ResponseHelper::response($th->getMessage(), 500);
        }
    }

    public function destroy(Product $product)
    {
        $res = $product;
        DB::beginTransaction();

        try {
            $id = Product::where('product_id', $product)->first();
            $id->delete();

            DB::commit();

            $res->deleted = true;
            return ResponseHelper::response($res);
        } catch (\Throwable $th) {
            DB::rollback();

            return ResponseHelper::response($th->getMessage(), 500);
        }
    }
}
