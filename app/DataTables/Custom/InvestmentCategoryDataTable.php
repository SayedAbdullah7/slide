<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\InvestmentCategory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class InvestmentCategoryDataTable extends BaseDataTable
{
    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Name'),
            Column::create('description')->setTitle('Description')->setSearchable(false)->setOrderable(false),
            Column::create('icon')->setTitle('Icon')->setSearchable(false)->setOrderable(false),
            Column::create('is_active')->setTitle('Status'),
            Column::create('created_at')->setTitle('Created')->setVisible(false),
            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false),
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
            'is_active' => Filter::select('Status', [
                '1' => 'Active',
                '0' => 'Inactive'
            ]),
            'created_at' => Filter::dateRange('Created Date Range'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = InvestmentCategory::query();

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.investment-category.columns._actions', compact('model'))->render();
            })
            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->editColumn('icon', function ($model) {
                if (!$model->icon) {
                    return '<span class="text-muted">-</span>';
                }
                return '<i class="' . e($model->icon) . ' fs-2"></i>';
            })
            ->editColumn('description', function ($model) {
                if (!$model->description) {
                    return '<span class="text-muted">-</span>';
                }
                $truncated = strlen($model->description) > 100 ? substr($model->description, 0, 100) . '...' : $model->description;
                return '<span data-bs-toggle="tooltip" title="' . e($model->description) . '">' . e($truncated) . '</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'is_active', 'icon', 'description'])
            ->make(true);
    }
}

