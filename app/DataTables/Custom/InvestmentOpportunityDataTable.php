<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\InvestmentOpportunity;

class InvestmentOpportunityDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        'category' => ['name'],
        'ownerProfile' => ['business_name'],

    ];

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setTitle('ID'),
            Column::create('name')->setTitle('Name')->setClassName('min-w-300px text-center'),
            Column::create('location')->setTitle('Location'),
            Column::create('description')->setTitle('Description')->setClassName('min-w-300px')->setVisible(false),
            Column::create('category_id')->setTitle('Category')->setVisible(false),
            Column::create('owner_profile_id')->setTitle('Owner')->setVisible(false),
            Column::create('status')->setTitle('Status'),
            Column::create('risk_level')->setTitle('Risk Level'),
            Column::create('target_amount')->setTitle('Target Amount'),
            Column::create('share_price')->setTitle('Price Per Share'),
            Column::create('reserved_shares')->setTitle('Reserved Shares')->setVisible(true),
            Column::create('completion_rate')->setTitle('Completion Rate')->setSearchable(false)->setOrderable(false),
            Column::create('investment_duration')->setTitle('Investment Duration')->setVisible(false),
            Column::create('expected_profit')->setTitle('Expected Profit Per Share')->setVisible(false),
            Column::create('expected_net_profit')->setTitle('Expected Net Profit Per Share')->setVisible(false),
            Column::create('shipping_fee_per_share')->setTitle('Shipping Fee Per Share')->setVisible(false),
            Column::create('actual_profit_per_share')->setTitle('Actual Profit Per Share')->setVisible(false),
            Column::create('actual_net_profit_per_share')->setTitle('Actual Net Profit Per Share')->setVisible(false),
            Column::create('distributed_profit')->setTitle('Distributed Profit')->setVisible(false),
            Column::create('min_investment')->setTitle('Min Investment'),
            Column::create('max_investment')->setTitle('Max Investment'),
            Column::create('fund_goal')->setTitle('Fund Goal'),
            Column::create('guarantee')->setTitle('Guarantee')->setVisible(false),
            Column::create('all_merchandise_delivered')->setTitle('Merchandise Delivered')->setVisible(false),
            Column::create('all_returns_distributed')->setTitle('Returns Distributed')->setVisible(false),
            Column::create('show')->setTitle('Show')->setVisible(false),
            Column::create('show_date')->setTitle('Show Date')->setVisible(false),
            Column::create('offering_start_date')->setTitle('Offering Start Date'),
            Column::create('offering_end_date')->setTitle('Offering End Date'),
            Column::create('profit_distribution_date')->setTitle('Profit Distribution Date')->setVisible(false),
            Column::create('expected_delivery_date')->setTitle('Expected Delivery Date')->setVisible(false),
            Column::create('expected_distribution_date')->setTitle('Expected Distribution Date')->setVisible(false),
            Column::create('created_at')->setTitle('Created')->setOrderable(false)->setVisible(false),
            Column::create('updated_at')->setTitle('Updated')->setOrderable(false)->setVisible(false),
            Column::create('deleted_at')->setTitle('Deleted At')->setVisible(false),
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
            // Select filters
            'status' => Filter::select('Status', \App\InvestmentStatusEnum::labels()),
            'risk_level' => Filter::select('Risk Level', collect(\App\RiskLevelEnum::cases())->mapWithKeys(fn($case) => [\App\RiskLevelEnum::text($case->value) => $case->value])->toArray()),
            'fund_goal' => Filter::select('Fund Goal', \App\FundGoalEnum::labels()),
            'show' => Filter::boolean('Show'),
            'all_merchandise_delivered' => Filter::boolean('Merchandise Delivered'),
            'all_returns_distributed' => Filter::boolean('Returns Distributed'),

            // Custom filter for completion_rate (computed attribute - needs custom query)
            'completion_rate' => Filter::selectCustom('Completion Rate', [
                '100' => '100% (Completed)',
                '75-99' => '75-99% (Almost Complete)',
                '50-74' => '50-74% (Half Complete)',
                '25-49' => '25-49% (Quarter Complete)',
                '0-24' => '0-24% (Just Started)'
            ], function ($query, $value) {
                // Calculate completion_rate using raw SQL since it's a computed attribute
                // completion_rate = (reserved_shares / total_shares) * 100
                // where total_shares = FLOOR(target_amount / share_price)
                // Only calculate when we have valid target_amount and share_price
                $rateCalculation = '(CASE
                    WHEN share_price > 0 AND target_amount > 0 THEN
                        (reserved_shares / NULLIF(FLOOR(target_amount / share_price), 0)) * 100
                    ELSE 0
                END)';

                switch ($value) {
                    case '100':
                        $query->whereRaw("{$rateCalculation} >= 100");
                        break;
                    case '75-99':
                        $query->whereRaw("{$rateCalculation} >= 75")
                              ->whereRaw("{$rateCalculation} < 100");
                        break;
                    case '50-74':
                        $query->whereRaw("{$rateCalculation} >= 50")
                              ->whereRaw("{$rateCalculation} < 75");
                        break;
                    case '25-49':
                        $query->whereRaw("{$rateCalculation} >= 25")
                              ->whereRaw("{$rateCalculation} < 50");
                        break;
                    case '0-24':
                        $query->whereRaw("{$rateCalculation} < 25");
                        break;
                }
            }),

            // Date filters
            'created_at' => Filter::dateRange('Created Date Range'),
            'show_date' => Filter::date('Show Date', 'today'),
            'offering_start_date' => Filter::dateRange('Offering Start Date Range'),
            'offering_end_date' => Filter::dateRange('Offering End Date Range'),
            'profit_distribution_date' => Filter::date('Profit Distribution Date', 'today'),
            'expected_delivery_date' => Filter::date('Expected Delivery Date', 'today'),
            'expected_distribution_date' => Filter::date('Expected Distribution Date', 'today'),
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
            ->editColumn('name', function ($model) {
                // use         Route::get('investments/{opportunity_id?}', [InvestmentController::class, 'index'])->name('investments.index');
                return '<a href="' . route('admin.investments.by-opportunity', $model->id) . '">' . $model->name . '</a>';
            })
            ->editColumn('status', function ($model) {
                $color = \App\InvestmentStatusEnum::color($model->status);
                $label = \App\InvestmentStatusEnum::label($model->status);
                return '<span class="badge badge-' . $color . '">' . $label . '</span>';
            })
            ->editColumn('risk_level', function ($model) {
                if (!$model->risk_level) return 'N/A';
                $color = \App\RiskLevelEnum::color($model->risk_level);
                $text = \App\RiskLevelEnum::text($model->risk_level);
                return '<span class="badge badge-' . $color . '">' . $text . '</span>';
            })
            ->editColumn('fund_goal', function ($model) {
                return $model->fund_goal ? \App\FundGoalEnum::label($model->fund_goal) : 'N/A';
            })
            ->editColumn('category_id', function ($model) {
                return $model->category ? $model->category->name : 'N/A';
            })
            ->editColumn('owner_profile_id', function ($model) {
                return $model->ownerProfile ? $model->ownerProfile->user->full_name : 'N/A';
            })

            ->editColumn('show', function ($model) {
                return $model->show
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-danger">No</span>';
            })
            ->editColumn('all_merchandise_delivered', function ($model) {
                return $model->all_merchandise_delivered
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-danger">No</span>';
            })
            ->editColumn('all_returns_distributed', function ($model) {
                return $model->all_returns_distributed
                    ? '<span class="badge badge-success">Yes</span>'
                    : '<span class="badge badge-danger">No</span>';
            })
            ->editColumn('target_amount', function ($model) {
                return $model->target_amount ? number_format($model->target_amount, 2) : 'N/A';
            })
            ->editColumn('share_price', function ($model) {
                return $model->share_price ? number_format($model->share_price, 2) : 'N/A';
            })
            ->editColumn('completion_rate', function ($model) {
                $rate = $model->completion_rate ?? 0;
                $color = $rate >= 100 ? 'success' : ($rate >= 75 ? 'warning' : ($rate >= 50 ? 'info' : 'danger'));

                return '<div class="d-flex align-items-center">
                    <div class="progress me-3" style="width: 100px; height: 20px;">
                        <div class="progress-bar bg-' . $color . '"
                             style="width: ' . min($rate, 100) . '%"
                             role="progressbar"
                             aria-valuenow="' . $rate . '"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <span class="fw-bold text-' . $color . '">' . number_format($rate, 1) . '%</span>
                </div>';
            })
            ->editColumn('expected_profit', function ($model) {
                return $model->expected_profit ? number_format($model->expected_profit, 2) : 'N/A';
            })
            ->editColumn('expected_net_profit', function ($model) {
                return $model->expected_net_profit ? number_format($model->expected_net_profit, 2) : 'N/A';
            })
            ->editColumn('shipping_fee_per_share', function ($model) {
                return $model->shipping_fee_per_share ? number_format($model->shipping_fee_per_share, 2) : 'N/A';
            })
            ->editColumn('actual_profit_per_share', function ($model) {
                return $model->actual_profit_per_share ? number_format($model->actual_profit_per_share, 2) : 'N/A';
            })
            ->editColumn('actual_net_profit_per_share', function ($model) {
                return $model->actual_net_profit_per_share ? number_format($model->actual_net_profit_per_share, 2) : 'N/A';
            })
            ->editColumn('distributed_profit', function ($model) {
                return $model->distributed_profit ? number_format($model->distributed_profit, 2) : 'N/A';
            })
            ->editColumn('min_investment', function ($model) {
                return $model->min_investment ? number_format($model->min_investment) : 'N/A';
            })
            ->editColumn('max_investment', function ($model) {
                return $model->max_investment ? number_format($model->max_investment) : 'N/A';
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
            ->editColumn('expected_delivery_date', function ($model) {
                return $model->expected_delivery_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('expected_distribution_date', function ($model) {
                return $model->expected_distribution_date?->format('Y-m-d H:i:s') ?? 'N/A';
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
                $this->applyFilters($query); // Auto-apply all filters
            },true)
            ->rawColumns(['status', 'risk_level', 'fund_goal', 'show', 'all_merchandise_delivered', 'all_returns_distributed', 'completion_rate', 'show_date', 'offering_start_date', 'offering_end_date', 'profit_distribution_date', 'expected_delivery_date', 'expected_distribution_date', 'created_at', 'updated_at', 'deleted_at', 'action', 'name'])
            ->make(true);
    }
}

