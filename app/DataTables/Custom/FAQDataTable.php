<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\FAQ;

class FAQDataTable extends BaseDataTable
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
            Column::create('question')->setTitle('Question'),
            Column::create('answer')->setTitle('Answer'),
            Column::create('order')->setTitle('Order'),
            Column::create('is_active')->setTitle('Status'),
            Column::create('created_at')->setTitle('Created')->setOrderable(false),
            Column::create('updated_at')->setTitle('Updated')->setOrderable(false),
            Column::create('actions')->setTitle('Actions')->setOrderable(false)->setSearchable(false),
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
                '1' => 'Yes',
                '0' => 'No'
            ]),
            'created_at' => Filter::date('Created', 'today'),
            'updated_at' => Filter::date('Updated', 'today'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = FAQ::query();

        return DataTables::of($query)
            ->editColumn('answer', function ($model) {
                return \Illuminate\Support\Str::limit(strip_tags($model->answer), 50);
            })
            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-danger">No</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('updated_at', function ($model) {
                return $model->updated_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->addColumn('actions', function ($model) {
                return view('pages.faq.columns._actions', compact('model'))->render();
            })
            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);
                if (!empty($filters['is_active'])) {
                    $query->where('is_active', $filters['is_active']);
                }
                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
                if (!empty($filters['updated_at'])) {
                    $query->whereDate('updated_at', $filters['updated_at']);
                }
            },true)
            ->rawColumns(['answer', 'is_active', 'created_at', 'updated_at', 'actions'])
            ->make(true);
    }
}
