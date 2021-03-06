<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query  = Customer::query();

        if ($request->filled('name')){
            $query->where(function ($qr) use ($request){
                $qr->where('customer_name', 'like', '%%'.$request->name.'%%');
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

        $data['data'] = new CustomerCollection($query);

        return ResponseHelper::response($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $customer    = Customer::create($request->all());

            DB::commit();
            return ResponseHelper::response($customer);
        }
        catch (\Throwable $th){
            DB::rollBack();
            return ResponseHelper::response($th->getMessage(), 500);
        }

    }

    public function show($Customer)
    {
        $data = Customer::where('customer_id', $Customer)->get();
        return ResponseHelper::response($data);
    }

    public function update(Request $request,$customer)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::where('customer_id', $customer)->first();
            $customer->customer_id = $request->customer_id;
            $customer->customer_name = $request->customer_name;
            $customer->address = $request->address;
            $customer->update();

            DB::commit();
            return ResponseHelper::response($customer);
        } catch (\Throwable $th) {
            DB::rollback();

            return ResponseHelper::response($th->getMessage(), 500);
        }
    }

    public function destroy($customer)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::findOrFail($customer);
            $customer->delete();
            DB::commit();
            return ResponseHelper::response("Data deleted successfully");
        } catch (\Throwable $th) {
            DB::rollback();

            return ResponseHelper::response("Data is not found", 404);
        }
    }
}
