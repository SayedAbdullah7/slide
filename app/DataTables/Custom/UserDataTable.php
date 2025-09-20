<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\User;

class UserDataTable extends BaseDataTable
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
            Column::create('id')->setOrderable(false),
            Column::create('full_name')->setTitle('Full Name'),
            Column::create('phone'),
            Column::create('email'),
            Column::create('national_id')->setTitle('National ID'),
            Column::create('birth_date')->setTitle('Birth Date'),
            Column::create('is_active')->setTitle('Status'),
            Column::create('active_profile_type')->setTitle('Profile Type'),
            Column::create('created_at')->setTitle('Created'),
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
            'created_at' => Filter::date('Created Date', 'today'),
            'is_active' => Filter::select('Status', [
                '1' => 'Active',
                '0' => 'Inactive'
            ]),
            'active_profile_type' => Filter::select('Profile Type', [
                'investor' => 'Investor',
                'owner' => 'Owner'
            ]),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = User::with(['investorProfile', 'ownerProfile']);

        return DataTables::of($query)
            ->addColumn('action', function ($model) {
                return view('pages.user.columns._actions', compact('model'))->render();
            })
            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->editColumn('active_profile_type', function ($model) {
                if (!$model->active_profile_type) {
                    return '<span class="badge badge-secondary">No Profile</span>';
                }

                return match ($model->active_profile_type) {
                    'investor' => '<span class="badge badge-primary">Investor</span>',
                    'owner' => '<span class="badge badge-info">Owner</span>',
                    default => '<span class="badge badge-secondary">Unknown</span>',
                };
            })
            ->editColumn('birth_date', function ($model) {
                return $model->birth_date?->format('Y-m-d') ?? 'N/A';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);

                if (!empty($filters['is_active'])) {
                    $query->where('is_active', $filters['is_active']);
                }

                if (!empty($filters['active_profile_type'])) {
                    $query->where('active_profile_type', $filters['active_profile_type']);
                }

                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
            })
            ->rawColumns(['action', 'is_active', 'active_profile_type'])
            ->make(true);
    }
}
