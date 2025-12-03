<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\BankDataTable;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BankDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.bank.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.bank.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:banks,code|max:50',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $bank = Bank::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Bank created successfully.',
            'data' => $bank
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bank $bank): View
    {
        return view('pages.bank.form', ['model' => $bank]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bank $bank): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:banks,code,' . $bank->id . '|max:50',
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $bank->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Bank updated successfully.',
            'data' => $bank
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank): JsonResponse
    {
        $bank->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Bank deleted successfully.'
        ]);
    }
}

