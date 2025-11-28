<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\InvestmentCategoryDataTable;
use App\Models\InvestmentCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InvestmentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InvestmentCategoryDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.investment-category.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.investment-category.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:investment_categories,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $category = InvestmentCategory::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment category created successfully.',
            'data' => $category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InvestmentCategory $investmentCategory): View
    {
        return view('pages.investment-category.form', ['model' => $investmentCategory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InvestmentCategory $investmentCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:investment_categories,name,' . $investmentCategory->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $investmentCategory->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investment category updated successfully.',
            'data' => $investmentCategory
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InvestmentCategory $investmentCategory): JsonResponse
    {
        $investmentCategory->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Investment category deleted successfully.'
        ]);
    }
}
