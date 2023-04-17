<?php

namespace App\Services;

use App\Models\ProductPurchase;
use App\Services\Contracts\StatisticsServiceInterface;
use App\Traits\Crud;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

class StatisticsService implements StatisticsServiceInterface
{
    use Crud;

    // public $modelClass = Example::class;

    // public function filter()
    // {
    //     return $this->modelClass::whereLike('name')
    //         ->whereEqual('key')
    //         ->whereBetween2('created_at')
    //         ->whereBetween2('updated_at')
    //         ->sort()
    //         ->customPaginate();
    // }

    public function interval_of_time($request)
    {
        return Purchase::with(['product_purchases.product'])
            ->whereBetween2('created_at', 'date')
            ->sort()
            ->customPaginate();
    }

    public function common_products($request)
    {
        $query = ProductPurchase::query();

        $results = $query->groupBy('product_id')
            ->select('product_id', DB::raw('SUM(count) as total_count'))
            ->whereBetween2('created_at', 'date')
            ->orderBy($request->get('orderBy', 'total_count'), $request->get('order', 'desc'))
            ->get();

        return $results;
    }


    public function product_stats($productId)
    {
        $query = ProductPurchase::query();
        $query = $query->where('product_id', $productId);

        $purchasesCount = $query->count();

        $totalPriceSum = $query->selectRaw('SUM(count * price) as total_price_sum')->first()->total_price_sum;

        return [
            'purchases_count' => $purchasesCount,
            'total_price_sum' => $totalPriceSum,
        ];
    }
}
