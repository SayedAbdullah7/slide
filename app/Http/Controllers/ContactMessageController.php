<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\ContactMessageDataTable;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContactMessageDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.contact-message.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.contact-message.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'profile_type' => 'required|in:' . implode(',', ContactMessage::PROFILE_TYPES),
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'status' => 'required|in:' . implode(',', ContactMessage::STATUSES),
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Contact message created successfully.',
            'data' => $message
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContactMessage $contactMessage): View
    {
        return view('pages.contact-message.form', ['model' => $contactMessage]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactMessage $contactMessage): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'profile_type' => 'required|in:' . implode(',', ContactMessage::PROFILE_TYPES),
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'status' => 'required|in:' . implode(',', ContactMessage::STATUSES),
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === ContactMessage::STATUS_RESOLVED && !$contactMessage->responded_at) {
            $validated['responded_at'] = now();
        }

        $contactMessage->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Contact message updated successfully.',
            'data' => $contactMessage
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Contact message deleted successfully.'
        ]);
    }
}

