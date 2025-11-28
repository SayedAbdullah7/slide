<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\UserDeletionRequestDataTable;
use App\Models\UserDeletionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserDeletionRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDeletionRequestDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.user-deletion-request.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Approve the deletion request
     */
    public function approve(Request $request, UserDeletionRequest $userDeletionRequest): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $userDeletionRequest->approve($validated['admin_notes'] ?? null);

        return response()->json([
            'status' => true,
            'msg' => 'User deletion request approved successfully.',
            'data' => $userDeletionRequest
        ]);
    }

    /**
     * Reject the deletion request
     */
    public function reject(Request $request, UserDeletionRequest $userDeletionRequest): JsonResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $userDeletionRequest->reject($validated['admin_notes'] ?? null);

        return response()->json([
            'status' => true,
            'msg' => 'User deletion request rejected successfully.',
            'data' => $userDeletionRequest
        ]);
    }

    /**
     * Cancel the deletion request
     */
    public function cancel(UserDeletionRequest $userDeletionRequest): JsonResponse
    {
        $userDeletionRequest->cancel();

        return response()->json([
            'status' => true,
            'msg' => 'User deletion request cancelled successfully.',
            'data' => $userDeletionRequest
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserDeletionRequest $userDeletionRequest): View
    {
        return view('pages.user-deletion-request.form', ['model' => $userDeletionRequest]);
    }

    /**
     * Show the rejection form
     */
    public function showRejectForm(UserDeletionRequest $userDeletionRequest): View
    {
        return view('pages.user-deletion-request.reject-form', ['model' => $userDeletionRequest]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserDeletionRequest $userDeletionRequest): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|in:' . implode(',', UserDeletionRequest::STATUSES),
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] !== $userDeletionRequest->status) {
            $validated['processed_at'] = now();
        }

        $userDeletionRequest->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'User deletion request updated successfully.',
            'data' => $userDeletionRequest
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserDeletionRequest $userDeletionRequest): JsonResponse
    {
        $userDeletionRequest->delete();

        return response()->json([
            'status' => true,
            'msg' => 'User deletion request deleted successfully.'
        ]);
    }
}

