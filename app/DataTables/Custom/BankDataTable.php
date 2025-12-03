<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Bank;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class BankDataTable extends BaseDataTable
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
            Column::create('code')->setTitle('Code'),
            Column::create('name_ar')->setTitle('Name (Arabic)'),
            Column::create('name_en')->setTitle('Name (English)'),
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
        $query = Bank::query();

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.bank.columns._actions', compact('model'))->render();
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
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'is_active', 'icon'])
            ->make(true);
    }
}

