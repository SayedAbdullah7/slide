<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ContactMessageDataTable extends BaseDataTable
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
            Column::create('profile_type')->setTitle('Profile Type'),
            Column::create('subject')->setTitle('Subject'),
            Column::create('message')->setTitle('Message')->setSearchable(false)->setOrderable(false),
            Column::create('status')->setTitle('Status'),
            Column::create('responded_at')->setTitle('Responded At'),
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
                ContactMessage::STATUS_PENDING => 'Pending',
                ContactMessage::STATUS_IN_PROGRESS => 'In Progress',
                ContactMessage::STATUS_RESOLVED => 'Resolved',
                ContactMessage::STATUS_CLOSED => 'Closed',
            ]),
            'profile_type' => Filter::select('Profile Type', [
                ContactMessage::PROFILE_TYPE_INVESTOR => 'Investor',
                ContactMessage::PROFILE_TYPE_OWNER => 'Owner',
                ContactMessage::PROFILE_TYPE_GUEST => 'Guest',
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
        $query = ContactMessage::with(['user']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.contact-message.columns._actions', compact('model'))->render();
            })
            ->addColumn('user_id', function ($model) {
                if (!$model->user) {
                    return '<span class="badge badge-light-secondary">Guest</span>';
                }
                return '<div class="d-flex flex-column">
                    <span class="fw-semibold">' . e($model->user->display_name) . '</span>
                    <span class="text-muted fs-8">' . e($model->user->phone) . '</span>
                </div>';
            })
            ->editColumn('profile_type', function ($model) {
                $badgeClass = match($model->profile_type) {
                    ContactMessage::PROFILE_TYPE_INVESTOR => 'primary',
                    ContactMessage::PROFILE_TYPE_OWNER => 'info',
                    ContactMessage::PROFILE_TYPE_GUEST => 'secondary',
                    default => 'secondary',
                };
                $label = ucfirst($model->profile_type);
                return '<span class="badge badge-' . $badgeClass . '">' . $label . '</span>';
            })
            ->editColumn('status', function ($model) {
                $badgeClass = match($model->status) {
                    ContactMessage::STATUS_PENDING => 'warning',
                    ContactMessage::STATUS_IN_PROGRESS => 'info',
                    ContactMessage::STATUS_RESOLVED => 'success',
                    ContactMessage::STATUS_CLOSED => 'secondary',
                    default => 'secondary',
                };
                return '<span class="badge badge-' . $badgeClass . '">' . ucfirst(str_replace('_', ' ', $model->status)) . '</span>';
            })
            ->editColumn('subject', function ($model) {
                return '<span class="fw-semibold">' . e($model->subject) . '</span>';
            })
            ->editColumn('message', function ($model) {
                $truncated = strlen($model->message) > 100 ? substr($model->message, 0, 100) . '...' : $model->message;
                return '<span data-bs-toggle="tooltip" title="' . e($model->message) . '">' . e($truncated) . '</span>';
            })
            ->editColumn('responded_at', function ($model) {
                return $model->responded_at ? $model->responded_at->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);
            }, true)
            ->rawColumns(['action', 'user_id', 'profile_type', 'status', 'subject', 'message'])
            ->make(true);
    }
}
