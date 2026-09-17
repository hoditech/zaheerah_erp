<?php

namespace App\Http\Controllers;

use App\Models\OnlineLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Enquiries that arrived from the public storefront (quote requests and
 * contact-form messages).
 *
 * Deliberately a plain Blade page rather than an Inertia page: it needs no
 * JavaScript build step, so it keeps working whatever state the Vue assets
 * are in.
 */
class OnlineLeadController extends Controller
{
    public function index(Request $request): View
    {
        $query = OnlineLead::query()->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        return view('online-leads', [
            'leads' => $query->paginate(25)->withQueryString(),
            'counts' => [
                'all' => OnlineLead::count(),
                'new' => OnlineLead::where('status', 'new')->count(),
                'quote' => OnlineLead::where('type', 'quote')->count(),
                'contact' => OnlineLead::where('type', 'contact')->count(),
            ],
            'status' => $status,
            'type' => $type,
        ]);
    }

    public function updateStatus(Request $request, OnlineLead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:new,contacted,archived',
        ]);

        $lead->update(['status' => $validated['status']]);

        return back()->with('success', "Lead #{$lead->id} marked as {$validated['status']}.");
    }
}
