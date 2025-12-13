<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Investment;

class InvestmentDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        'investor' => ['full_name'],
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
            Column::create('opportunity_name')->setTitle('Opportunity')->setSearchable(false)->setOrderable(false),
            Column::create('opportunity_completion_rate')->setTitle('Opportunity Completion')->setSearchable(false)->setOrderable(false)->setVisible(true),
            Column::create('investor_name')->setTitle('Investor')->setSearchable(false)->setOrderable(false),
            Column::create('shares')->setTitle('Shares'),
            Column::create('share_price')->setTitle('Share Price'),
            Column::create('total_investment')->setTitle('Total Investment'),
            Column::create('investment_type')->setTitle('Type'),
            Column::create('status')->setTitle('Status'),
            Column::create('investment_date')->setTitle('Investment Date'),
            Column::create('merchandise_status')->setTitle('Merchandise Status')->setVisible(false),
            Column::create('distribution_status')->setTitle('Distribution Status')->setVisible(false),
            Column::create('expected_profit_per_share')->setTitle('Expected Profit')->setVisible(false),
            Column::create('actual_profit_per_share')->setTitle('Actual Profit')->setVisible(false),
            Column::create('distributed_profit')->setTitle('Distributed Profit')->setVisible(false),
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
        $investors = \App\Models\InvestorProfile::with('user')->get()->mapWithKeys(function ($investor) {
            $name = $investor->user->display_name ?? $investor->full_name ?? 'Investor #' . $investor->id;
            return [$investor->id => $name];
        })->toArray();

        return [
            'opportunity_id' => Filter::select('Opportunity', \App\Models\InvestmentOpportunity::pluck('name', 'id')->toArray()),
            'investor_id' => Filter::select('Investor', $investors),
            'status' => Filter::select('Status', [
                'pending' => 'Pending',
                'active' => 'Active',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]),
            'investment_type' => Filter::select('Investment Type', [
                'myself' => 'Myself',
                'authorize' => 'Authorize'
            ]),
            'merchandise_status' => Filter::select('Merchandise Status', [
                'pending' => 'Pending',
                'arrived' => 'Arrived'
            ]),
            'distribution_status' => Filter::select('Distribution Status', [
                'pending' => 'Pending',
                'distributed' => 'Distributed'
            ]),
            'investment_date' => Filter::date('Investment Date', 'today'),
            'created_at' => Filter::date('Created', 'today'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($opportunityId = null, $investorId = null)
    {
        $query = Investment::with(['opportunity', 'investorProfile.user']);

        // Filter by opportunity ID if provided
        if ($opportunityId) {
            $query->where('opportunity_id', $opportunityId);
        }

        // Filter by investor ID if provided
        if ($investorId) {
            $query->where('investor_id', $investorId);
        }

        return DataTables::of($query)
            ->editColumn('action', function ($model) {
                return view('pages.investment.columns._actions', compact('model'))->render();
            })
                ->editColumn('opportunity_name', function ($model) {
                    return $model->opportunity->name ?? 'N/A';
                })
                ->editColumn('opportunity_completion_rate', function ($model) {
                    if (!$model->opportunity) return 'N/A';

                    $rate = $model->opportunity->completion_rate ?? 0;
                    $color = $rate >= 100 ? 'success' : ($rate >= 75 ? 'warning' : ($rate >= 50 ? 'info' : 'danger'));

                    return '<div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 80px; height: 15px;">
                            <div class="progress-bar bg-' . $color . '"
                                 style="width: ' . min($rate, 100) . '%"
                                 role="progressbar"
                                 aria-valuenow="' . $rate . '"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <span class="fw-bold text-' . $color . '" style="font-size: 12px;">' . number_format($rate, 0) . '%</span>
                    </div>';
                })
                ->editColumn('investor_name', function ($model) {
                return $model->investorProfile->user->display_name ?? 'N/A';
            })
            ->editColumn('shares', function ($model) {
                return number_format($model->shares);
            })
            ->editColumn('share_price', function ($model) {
                return '$' . number_format($model->share_price, 2);
            })
            ->editColumn('total_investment', function ($model) {
                return '$' . number_format($model->total_investment, 2);
            })
            ->editColumn('investment_type', function ($model) {
                $types = [
                    'myself' => [
                        'badge' => 'badge-light-primary',
                        'icon' => 'fa-user',
                        'icon_color' => 'text-primary',
                        'label' => 'Myself',
                        'text_color' => 'text-primary',
                    ],
                    'authorize' => [
                        'badge' => 'badge-light-success',
                        'icon' => 'fa-handshake',
                        'icon_color' => 'text-success',
                        'label' => 'Authorize',
                        'text_color' => 'text-success',
                    ],
                ];

                $type = $types[$model->investment_type] ?? null;

                if ($type) {
                    return sprintf(
                        '<span class="badge %s d-inline-flex align-items-center px-2 py-1" style="font-size:13px;">
                            <i class="fa-solid %s me-1 %s" style="font-size: 14px;"></i>
                            <span class="fw-semibold %s">%s</span>
                        </span>',
                        $type['badge'],
                        $type['icon'],
                        $type['icon_color'],
                        $type['text_color'],
                        $type['label']
                    );
                }

                return '<span class="badge badge-light-secondary">Unknown</span>';
            })
            ->editColumn('status', function ($model) {
                return match ($model->status) {
                    'active' => '<span class="badge badge-success">Active</span>',
                    'completed' => '<span class="badge badge-info">Completed</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    default => '<span class="badge badge-light">' . ucfirst($model->status) . '</span>',
                };
            })
            ->editColumn('investment_date', function ($model) {
                return $model->investment_date?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->editColumn('merchandise_status', function ($model) {
                if (!$model->merchandise_status) return 'N/A';
                $color = $model->merchandise_status === 'arrived' ? 'success' : 'warning';
                return '<span class="badge badge-' . $color . '">' . ucfirst($model->merchandise_status) . '</span>';
            })
            ->editColumn('distribution_status', function ($model) {
                if (!$model->distribution_status) return 'N/A';
                $color = $model->distribution_status === 'distributed' ? 'success' : 'warning';
                return '<span class="badge badge-' . $color . '">' . ucfirst($model->distribution_status) . '</span>';
            })
            ->editColumn('expected_profit_per_share', function ($model) {
                return $model->expected_profit_per_share ? '$' . number_format($model->expected_profit_per_share, 2) : 'N/A';
            })
            ->editColumn('actual_profit_per_share', function ($model) {
                return $model->actual_profit_per_share ? '$' . number_format($model->actual_profit_per_share, 2) : 'N/A';
            })
            ->editColumn('distributed_profit', function ($model) {
                return $model->distributed_profit ? '$' . number_format($model->distributed_profit, 2) : 'N/A';
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at?->format('Y-m-d H:i:s') ?? 'N/A';
            })
            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);

                // Filter by opportunity_id if provided in URL
                $opportunityId = request()->get('opportunity_id');
                if ($opportunityId) {
                    $query->where('opportunity_id', $opportunityId);
                }

                // Filter by investor_id if provided in URL
                $investorId = request()->get('investor_id');
                if ($investorId) {
                    $query->where('investor_id', $investorId);
                }

                if (!empty($filters['opportunity_id'])) {
                    $query->where('opportunity_id', $filters['opportunity_id']);
                }
                if (!empty($filters['investor_id'])) {
                    $query->where('investor_id', $filters['investor_id']);
                }
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }
                // if (!empty($filters['investment_type'])) {
                //     $query->where('investment_type', $filters['investment_type']);
                // }
                if (!empty($filters['merchandise_status'])) {
                    $query->where('merchandise_status', $filters['merchandise_status']);
                }
                if (!empty($filters['distribution_status'])) {
                    $query->where('distribution_status', $filters['distribution_status']);
                }
                if (!empty($filters['investment_date'])) {
                    $query->whereDate('investment_date', $filters['investment_date']);
                }
                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
            },true)
            ->rawColumns(['opportunity_completion_rate', 'investment_type', 'status', 'merchandise_status', 'distribution_status', 'action'])
            ->make(true);
    }
}
