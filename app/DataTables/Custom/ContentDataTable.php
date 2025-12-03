<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Content;

class ContentDataTable extends BaseDataTable
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
            Column::create('type')->setTitle('Type'),
            Column::create('title')->setTitle('Title'),
            Column::create('content')->setTitle('Content'),
            Column::create('last_updated')->setTitle('Last updated'),
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
            'type' => Filter::select('Type', \App\Models\Content::getContentTypes()),
            'last_updated' => Filter::date('Last updated', 'today'),
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
        $query = Content::query();

        return DataTables::of($query)
            ->editColumn('type', function ($model) {
                $types = \App\Models\Content::getContentTypes();
                return $types[$model->type] ?? $model->type;
            })
            ->editColumn('content', function ($model) {
                return \Illuminate\Support\Str::limit(strip_tags($model->content), 50);
            })
            ->editColumn('last_updated', function ($model) {
                return $model->last_updated?->format('Y-m-d H:i:s') ?? 'N/A';
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
                return view('pages.content.columns._actions', compact('model'))->render();
            })
            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);
                if (!empty($filters['type'])) {
                    $query->where('type', $filters['type']);
                }
                if (!empty($filters['last_updated'])) {
                    $query->whereDate('last_updated', $filters['last_updated']);
                }
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
            ->rawColumns(['type', 'content', 'last_updated', 'is_active', 'created_at', 'updated_at', 'actions'])
            ->make(true);
    }
}
