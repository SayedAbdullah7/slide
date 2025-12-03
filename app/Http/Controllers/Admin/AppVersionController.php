<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AppVersionController extends Controller
{
    /**
     * Display a listing of app versions
     * عرض قائمة إصدارات التطبيق
     */
    public function index(\App\DataTables\Custom\AppVersionDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.app-version.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new app version
     * عرض نموذج إنشاء إصدار جديد
     */
    public function create(): View
    {
        return view('pages.app-version.form');
    }

    /**
     * Store a newly created app version
     * حفظ إصدار جديد
     */
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Prepare request data: convert OS to lowercase
        $os = strtolower($request->input('os', ''));
        $request->merge([
            'os' => $os
        ]);

        $validated = $request->validate([
            'version' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('app_versions', 'version')
                    ->where('os', $os)
            ],
            'os' => 'required|string|in:ios,android',
            'is_mandatory' => 'boolean',
            'release_notes' => 'nullable|string',
            'release_notes_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'released_at' => 'nullable|date',
        ]);

        $validated['is_mandatory'] = $request->has('is_mandatory') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $version = AppVersion::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'تم إنشاء الإصدار بنجاح',
                    'data' => $version
                ]);
            }

            return redirect()->route('admin.app-versions.index')
                ->with('success', 'تم إنشاء الإصدار بنجاح');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'فشل في إنشاء الإصدار: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في إنشاء الإصدار: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified app version
     * عرض تفاصيل إصدار معين
     */
    public function show(AppVersion $appVersion): View
    {
        return view('pages.app-version.show', ['appVersion' => $appVersion, 'model' => $appVersion]);
    }

    /**
     * Show the form for editing the specified app version
     * عرض نموذج تعديل إصدار
     */
    public function edit(AppVersion $appVersion): View
    {
        return view('pages.app-version.form', ['model' => $appVersion]);
    }

    /**
     * Update the specified app version
     * تحديث إصدار معين
     */
    public function update(Request $request, AppVersion $appVersion): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        // Prepare request data: convert OS to lowercase
        $os = strtolower($request->input('os', ''));
        $request->merge([
            'os' => $os
        ]);

        $validated = $request->validate([
            'version' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('app_versions', 'version')
                    ->where('os', $os)
                    ->ignore($appVersion->id)
            ],
            'os' => 'required|string|in:ios,android',
            'is_mandatory' => 'boolean',
            'release_notes' => 'nullable|string',
            'release_notes_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'released_at' => 'nullable|date',
        ]);

        $validated['is_mandatory'] = $request->has('is_mandatory') ? 1 : 0;
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        try {
            $appVersion->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'تم تحديث الإصدار بنجاح',
                    'data' => $appVersion
                ]);
            }

            return redirect()->route('admin.app-versions.index')
                ->with('success', 'تم تحديث الإصدار بنجاح');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'فشل في تحديث الإصدار: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في تحديث الإصدار: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified app version
     * حذف إصدار معين
     */
    public function destroy(Request $request, AppVersion $appVersion): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $appVersion->delete();

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => 'تم حذف الإصدار بنجاح'
                ]);
            }

            return redirect()->route('admin.app-versions.index')
                ->with('success', 'تم حذف الإصدار بنجاح');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'فشل في حذف الإصدار: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'فشل في حذف الإصدار: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     * تفعيل/إلغاء تفعيل إصدار
     */
    public function toggleStatus(Request $request, AppVersion $appVersion): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $isActive = $request->input('is_active', !$appVersion->is_active);
            $appVersion->update([
                'is_active' => $isActive
            ]);

            $status = $appVersion->is_active ? 'مفعل' : 'معطل';

            if ($request->ajax()) {
                return response()->json([
                    'status' => true,
                    'msg' => "تم $status الإصدار بنجاح",
                    'data' => $appVersion
                ]);
            }

            return redirect()->back()
                ->with('success', "تم $status الإصدار بنجاح");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'فشل في تغيير حالة الإصدار: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'فشل في تغيير حالة الإصدار: ' . $e->getMessage());
        }
    }
}
