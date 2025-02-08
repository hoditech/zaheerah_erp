<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    // Get all notices
    public function index()
    {
        $notices = Notice::all();
        return response()->json($notices);
    }

    // Store a new notice
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);

        $notice = Notice::create($request->all());

        // Log the activity
        log_activity('Create Notice', 'Created notice: ' . $notice->title);

        return response()->json($notice, 201);
    }

    // Update a notice
    public function update(Request $request, Notice $notice)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'date' => 'sometimes|date',
        ]);

        $notice->update($request->all());

        // Log the activity
        log_activity('Update Notice', 'Updated notice: ' . $notice->title);

        return response()->json($notice);
    }

    // Delete a notice
    public function destroy(Notice $notice)
    {
        // Log the activity before deleting
        log_activity('Delete Notice', 'Deleted notice: ' . $notice->title);

        $notice->delete();
        return response()->json(null, 204);
    }
}