<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\UserDeletionRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class UserDeletionRequestDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        'user' => ['phone', 'email'],
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
            Column::create('user_id')->setTitle('User')->setSearchable(false)->setOrderable(false),
            Column::create('reason')->setTitle('Reason')->setSearchable(false),
            Column::create('status')->setTitle('Status'),
            Column::create('requested_at')->setTitle('Requested At'),
            Column::create('processed_at')->setTitle('Processed At'),
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
            'status' => Filter::select('Status', [
                UserDeletionRequest::STATUS_PENDING => 'Pending',
                UserDeletionRequest::STATUS_CANCELLED => 'Cancelled',
                UserDeletionRequest::STATUS_APPROVED => 'Approved',
                UserDeletionRequest::STATUS_REJECTED => 'Rejected',
            ]),
            'requested_at' => Filter::dateRange('Requested Date Range'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = UserDeletionRequest::with(['user']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.user-deletion-request.columns._actions', compact('model'))->render();
            })
            ->addColumn('user_id', function ($model) {
                if (!$model->user) {
                    return '<span class="text-muted">N/A</span>';
                }
                return '<div class="d-flex flex-column">
                    <span class="fw-semibold">' . e($model->user->display_name) . '</span>
                    <span class="text-muted fs-8">' . e($model->user->phone) . '</span>
                </div>';
            })
            ->editColumn('status', function ($model) {
                $badgeClass = match($model->status) {
                    UserDeletionRequest::STATUS_PENDING => 'warning',
                    UserDeletionRequest::STATUS_CANCELLED => 'secondary',
                    UserDeletionRequest::STATUS_APPROVED => 'success',
                    UserDeletionRequest::STATUS_REJECTED => 'danger',
                    default => 'secondary',
                };
                return '<span class="badge badge-' . $badgeClass . '">' . ucfirst($model->status) . '</span>';
            })
            ->editColumn('requested_at', function ($model) {
                return $model->requested_at ? $model->requested_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('processed_at', function ($model) {
                return $model->processed_at ? $model->processed_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('reason', function ($model) {
                if (!$model->reason) {
                    return '<span class="text-muted">-</span>';
                }
                $truncated = strlen($model->reason) > 50 ? substr($model->reason, 0, 50) . '...' : $model->reason;
                return '<span data-bs-toggle="tooltip" title="' . e($model->reason) . '">' . e($truncated) . '</span>';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'user_id', 'status', 'reason'])
            ->make(true);
    }
}













