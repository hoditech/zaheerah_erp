<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnlineLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bulk-quote requests and contact messages from the public storefront.
 */
class LeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_ref' => 'nullable|string|max:255',
            'type' => 'required|string|in:quote,contact,general',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:5000',
            'payload' => 'nullable|array',
        ]);

        if (! empty($validated['external_ref'])) {
            $existing = OnlineLead::where('external_ref', $validated['external_ref'])->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Lead already recorded.',
                    'duplicate' => true,
                    'data' => ['id' => $existing->id],
                ], 200);
            }
        }

        $lead = OnlineLead::create([
            'type' => $validated['type'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'] ?? null,
            'payload' => $validated['payload'] ?? null,
            'source' => 'storefront',
            'external_ref' => $validated['external_ref'] ?? null,
            'status' => 'new',
        ]);

        return response()->json([
            'message' => 'Lead recorded.',
            'data' => ['id' => $lead->id],
        ], 201);
    }

    /**
     * GET /api/v1/leads - lets staff tooling pull what is waiting.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OnlineLead::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        return response()->json(['data' => $query->limit(200)->get()]);
    }
}
