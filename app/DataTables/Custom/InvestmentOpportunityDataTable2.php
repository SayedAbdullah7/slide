<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\InvestmentOpportunity;

class InvestmentOpportunityDataTable2 extends BaseDataTable
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
            Column::create('name')->setTitle('Name'),
            Column::create('location')->setTitle('Location'),
            Column::create('description')->setTitle('Description'),
            Column::create('category_id')->setTitle('Category id'),
            Column::create('owner_profile_id')->setTitle('Owner profile id'),
            Column::create('status')->setTitle('Status'),
            Column::create('risk_level')->setTitle('Risk level'),
            Column::create('target_amount')->setTitle('Target amount'),
            Column::create('price_per_share')->setTitle('Price per share'),
            Column::create('reserved_shares')->setTitle('Reserved shares'),
            Column::create('investment_duration')->setTitle('Investment duration'),
            Column::create('expected_return_amount_by_myself')->setTitle('Expected return amount by myself'),
            Column::create('expected_net_return_by_myself')->setTitle('Expected net return by myself'),
            Column::create('expected_return_amount_by_authorize')->setTitle('Expected return amount by authorize'),
            Column::create('expected_net_return_by_authorize')->setTitle('Expected net return by authorize'),
            Column::create('shipping_and_service_fee')->setTitle('Shipping and service fee'),
            Column::create('min_investment')->setTitle('Min investment'),
            Column::create('max_investment')->setTitle('Max investment'),
            Column::create('fund_goal')->setTitle('Fund goal'),
            Column::create('guarantee')->setTitle('Guarantee'),
            Column::create('show')->setTitle('Show'),
            Column::create('show_date')->setTitle('Show date'),
            Column::create('offering_start_date')->setTitle('Offering start date'),
            Column::create('offering_end_date')->setTitle('Offering end date'),
            Column::create('profit_distribution_date')->setTitle('Profit distribution date'),
            Column::create('created_at')->setTitle('Created')->setOrderable(false),
            Column::create('updated_at')->setTitle('Updated')->setOrderable(false),
            Column::create('deleted_at')->setTitle('Deleted at'),
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
                // Add your enum values here
            ]),
            'show' => Filter::select('Show', [
                '1' => 'Yes',
                '0' => 'No'
            ]),
            'show_date' => Filter::date('Show date', 'today'),
            'offering_start_date' => Filter::date('Offering start date', 'today'),
            'offering_end_date' => Filter::date('Offering end date', 'today'),
            'profit_distribution_date' => Filter::date('Profit distribution date', 'today'),
            'created_at' => Filter::date('Created', 'today'),
            'updated_at' => Filter::date('Updated', 'today'),
            'deleted_at' => Filter::date('Deleted at', 'today'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = InvestmentOpportunity::query();

        return DataTables::of($query)
        ->editColumn('action', function ($model) {
            return view('pages.investment-opportunity.columns._actions', compact('model'))->render();
        })
            ->editColumn('status', function ($model) {
                return match ($model->status) {
                    'active' => '<span class="badge badge-success">Active</span>',
                    'inactive' => '<span class="badge badge-danger">Inactive</span>',
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    default => '<span class="badge badge-light">' . ucfirst($model->status) . '</span>',
                };
            })
            ->editColumn('show', function ($model) {
                return $model->show
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-danger">No</span>';
            })
            ->editColumn('show_date', function ($model) {
                return $model->show_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('offering_start_date', function ($model) {
                return $model->offering_start_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('offering_end_date', function ($model) {
                return $model->offering_end_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('profit_distribution_date', function ($model) {
                return $model->profit_distribution_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('updated_at', function ($model) {
                return $model->updated_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('deleted_at', function ($model) {
                return $model->deleted_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                if (!empty($filters['show'])) {
                    $query->where('show', $filters['show']);
                }
                if (!empty($filters['show_date'])) {
                    $query->whereDate('show_date', $filters['show_date']);
                }
                if (!empty($filters['offering_start_date'])) {
                    $query->whereDate('offering_start_date', $filters['offering_start_date']);
                }
                if (!empty($filters['offering_end_date'])) {
                    $query->whereDate('offering_end_date', $filters['offering_end_date']);
                }
                if (!empty($filters['profit_distribution_date'])) {
                    $query->whereDate('profit_distribution_date', $filters['profit_distribution_date']);
                }
                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
                if (!empty($filters['updated_at'])) {
                    $query->whereDate('updated_at', $filters['updated_at']);
                }
                if (!empty($filters['deleted_at'])) {
                    $query->whereDate('deleted_at', $filters['deleted_at']);
                }
            })
            ->rawColumns(['status', 'show', 'show_date', 'offering_start_date', 'offering_end_date', 'profit_distribution_date', 'created_at', 'updated_at', 'deleted_at', 'action'])
            ->make(true);
    }
}
