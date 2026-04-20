<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Phase 1 — returns empty notifications.
     * Invoice/payment notifications will be added in Phase 4.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'count'         => 0,
            'notifications' => [],
        ]);
    }
}
