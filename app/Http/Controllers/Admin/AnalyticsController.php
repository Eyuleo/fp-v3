<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        // Date range filter
        $fromDate = $request->input('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate   = $request->input('to_date', now()->format('Y-m-d'));

        // GMV (Gross Merchandise Value) - total value of completed orders
        $gmv = Order::where('status', 'completed')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('price');

        // Order count by status
        $ordersByStatus = Order::whereBetween('created_at', [$fromDate, $toDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Total orders
        $totalOrders = array_sum($ordersByStatus);

        // Active students (students with active services OR orders in the date range)
        $activeStudents = User::where('role', 'student')
            ->where('is_active', true)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereHas('services', function ($q) {
                    $q->where('is_active', true);
                })->orWhereHas('ordersAsStudent', function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('created_at', [$fromDate, $toDate]);
                });
            })
            ->count();

        // On-time delivery rate
        $deliveredOrders = Order::whereIn('status', ['delivered', 'completed'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotNull('delivery_date')
            ->get();

        $onTimeCount = $deliveredOrders->filter(function ($order) {
                                                     // Check if order was delivered on or before the delivery date
            $deliveryTimestamp = $order->updated_at; // Assuming updated_at reflects delivery time
            return $deliveryTimestamp <= $order->delivery_date;
        })->count();

        $onTimeDeliveryRate = $deliveredOrders->count() > 0
            ? round(($onTimeCount / $deliveredOrders->count()) * 100, 1)
            : 0;

        // Dispute rate
        $completedOrdersCount = Order::where('status', 'completed')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $disputesCount = Dispute::whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        $disputeRate = $completedOrdersCount > 0
            ? round(($disputesCount / $completedOrdersCount) * 100, 1)
            : 0;

        // Revenue breakdown (platform commission)
        $totalCommission = Order::where('status', 'completed')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('commission');

        // Top performing students
        $topStudents = User::where('role', 'student')
            ->withCount(['ordersAsStudent as completed_orders' => function ($query) use ($fromDate, $toDate) {
                $query->where('status', 'completed')
                    ->whereBetween('created_at', [$fromDate, $toDate]);
            }])
            ->withSum(['ordersAsStudent as total_earnings' => function ($query) use ($fromDate, $toDate) {
                $query->where('status', 'completed')
                    ->whereBetween('created_at', [$fromDate, $toDate]);
            }], 'price')
            ->having('completed_orders', '>', 0)
            ->orderBy('total_earnings', 'desc')
            ->take(10)
            ->get();

        // Recent activity
        $recentOrders = Order::with(['service', 'student', 'client'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.analytics', compact(
            'gmv',
            'ordersByStatus',
            'totalOrders',
            'activeStudents',
            'onTimeDeliveryRate',
            'disputeRate',
            'totalCommission',
            'topStudents',
            'recentOrders',
            'fromDate',
            'toDate'
        ));
    }
}
