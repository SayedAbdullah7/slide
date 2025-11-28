<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\FAQDataTable;
use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(FAQDataTable $dataTable, Request $request)
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.faq.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.faq.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            FAQ::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'FAQ created successfully'
                ]);
            }

            return redirect()->route('admin.faqs.index')
                ->with('success', 'FAQ created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to create FAQ: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create FAQ');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FAQ $faq)
    {
        return view('pages.faq.show', compact('faq'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FAQ $faq)
    {
        return view('pages.faq.form', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FAQ $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $faq->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'FAQ updated successfully'
                ]);
            }

            return redirect()->route('admin.faqs.index')
                ->with('success', 'FAQ updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to update FAQ: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update FAQ');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FAQ $faq)
    {
        try {
            $faq->delete();

            if (request()->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'FAQ deleted successfully'
                ]);
            }

            return redirect()->route('admin.faqs.index')
                ->with('success', 'FAQ deleted successfully');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to delete FAQ: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete FAQ');
        }
    }
}
