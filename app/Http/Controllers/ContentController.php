<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\ContentDataTable;
use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContentDataTable $dataTable, Request $request)
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.content.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contentTypes = Content::getContentTypes();
        return view('pages.content.form', compact('contentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(Content::getContentTypes())),
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'last_updated' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['last_updated'] = $validated['last_updated'] ?? now();

        try {
            Content::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Content created successfully'
                ]);
            }

            return redirect()->route('admin.contents.index')
                ->with('success', 'Content created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to create content: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create content');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Content $content)
    {
        return view('pages.content.show', compact('content'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Content $content)
    {
        $contentTypes = Content::getContentTypes();
        return view('pages.content.form', compact('content', 'contentTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Content $content)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(Content::getContentTypes())),
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean',
            'last_updated' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['last_updated'] = $validated['last_updated'] ?? now();

        try {
            $content->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Content updated successfully'
                ]);
            }

            return redirect()->route('admin.contents.index')
                ->with('success', 'Content updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to update content: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update content');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        try {
            $content->delete();

            if (request()->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'Content deleted successfully'
                ]);
            }

            return redirect()->route('admin.contents.index')
                ->with('success', 'Content deleted successfully');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Failed to delete content: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to delete content');
        }
    }
}
