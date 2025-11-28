<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\DataTables\Custom\TransactionDataTable;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TransactionDataTable $dataTable, Request $request, $userId = null): JsonResponse|View
    {
        // Also check query string for backward compatibility
        if (!$userId) {
            $userId = $request->get('user_id');
        }

        if ($request->ajax()) {
            return $dataTable->handle($userId);
        }

        $user = null;
        $hasInvestor = false;
        $hasOwner = false;
        $investorBalance = 0;
        $ownerBalance = 0;

        if ($userId) {
            $user = \App\Models\User::with(['investorProfile', 'ownerProfile'])->find($userId);

            if ($user) {
                $hasInvestor = $user->investorProfile !== null;
                $hasOwner = $user->ownerProfile !== null;
                $investorBalance = $hasInvestor ? $user->investorProfile->getWalletBalance() : 0;
                $ownerBalance = $hasOwner ? $user->ownerProfile->getWalletBalance() : 0;
            }
        }

        return view('pages.transaction.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
            'userId' => $userId,
            'user' => $user,
            'hasInvestor' => $hasInvestor,
            'hasOwner' => $hasOwner,
            'investorBalance' => $investorBalance,
            'ownerBalance' => $ownerBalance,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.transaction.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        // Eager load the payable relationship
        $transaction->load(['payable']);

        return view('pages.transaction.show', ['transaction' => $transaction]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        return view('pages.transaction.form', compact('transaction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
