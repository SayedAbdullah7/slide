<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\AppVersion;

class AppVersionDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        //
    ];

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('version')->setTitle('الإصدار'),
            Column::create('os')->setTitle('نظام التشغيل'),
            Column::create('is_mandatory')->setTitle('إجباري'),
            Column::create('is_active')->setTitle('الحالة'),
            Column::create('released_at')->setTitle('تاريخ الإصدار'),
            Column::create('created_at')->setTitle('تاريخ الإنشاء'),
            Column::create('action')->setTitle('الإجراءات')->setSearchable(false)->setOrderable(false),
        ];
    }

    /**
     * Get the filters for the DataTable.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            'os' => Filter::select('نظام التشغيل', [
                'ios' => 'iOS',
                'android' => 'Android'
            ]),
            'is_active' => Filter::select('الحالة', [
                '1' => 'مفعل',
                '0' => 'معطل'
            ]),
            'is_mandatory' => Filter::select('نوع التحديث', [
                '1' => 'إجباري',
                '0' => 'اختياري'
            ]),
            'version' => Filter::text('الإصدار', 'ابحث بالإصدار...'),
            'released_at' => Filter::dateRange('تاريخ الإصدار'),
            'created_at' => Filter::dateRange('تاريخ الإنشاء'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = AppVersion::query();

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.app-version.columns._actions', compact('model'))->render();
            })
            ->editColumn('os', function ($model) {
                if ($model->os == 'ios') {
                    return '<span class="badge badge-light-primary">
                        <i class="fa-brands fa-apple me-1"></i>iOS
                    </span>';
                } else {
                    return '<span class="badge badge-light-success">
                        <i class="fa-brands fa-android me-1"></i>Android
                    </span>';
                }
            })
            ->editColumn('is_mandatory', function ($model) {
                return $model->is_mandatory
                    ? '<span class="badge badge-danger">إجباري</span>'
                    : '<span class="badge badge-light">اختياري</span>';
            })
            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge badge-success">مفعل</span>'
                    : '<span class="badge badge-danger">معطل</span>';
            })
            ->editColumn('released_at', function ($model) {
                return $model->released_at ? $model->released_at->format('Y-m-d') : 'N/A';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            }, true)
            ->rawColumns(['action', 'os', 'is_mandatory', 'is_active'])
            ->make(true);
    }
}


