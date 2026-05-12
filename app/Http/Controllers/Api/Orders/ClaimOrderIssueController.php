<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ClaimOrderIssueController extends Controller
{
    public function __invoke(Request $request, $orderId)
    {
        $request->validate([
            'issue' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Check if user can claim issue (e.g. status is delivered)
        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'You can only claim issues for delivered orders.'], 403);
        }

        $order->update([
            'has_claim_issue' => true,
            'claim_issue' => $request->issue,
        ]);

        return response()->json([
            'message' => 'Issue claimed successfully.',
            'order' => $order->formatForDetail(),
        ]);
    }
}
