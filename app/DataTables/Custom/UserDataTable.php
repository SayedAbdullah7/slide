<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class UserDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [
        'investorProfile' => ['full_name', 'national_id'],
        'ownerProfile' => ['business_name', 'tax_number'],
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
            Column::create('display_name')->setTitle('Name')->setSearchable(false),
            Column::create('phone'),
            Column::create('email'),
            Column::create('user_profiles')->setTitle('User Profiles')->setSearchable(false)->setOrderable(false),
            Column::create('wallet_balance')->setTitle('Wallet Balance')->setSearchable(false)->setOrderable(false),
            Column::create('is_active')->setTitle('Status'),
            Column::create('active_profile_type')->setTitle('Active Profile')->setSearchable(false),
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
            // Select filter - dropdown with options
            'is_active' => Filter::select('Status', [
                '1' => 'Active',
                '0' => 'Inactive'
            ]),

            // Date filter - single date
            // 'created_at' => Filter::date('Created Date', 'today'),

            // Date range filter - from/to dates
            'created_at' => Filter::dateRange('Created Date Range'),

            // Text filter - search in email
            'email' => Filter::text('Email', 'Enter email...'),

            // Select filter - active profile type
            'active_profile_type' => Filter::select('Active Profile', [
                'investor' => 'Investor (Active)',
                'owner' => 'Owner (Active)'
            ]),

            // Custom query filter - Has Profile with custom logic
            'has_profile' => Filter::selectCustom('Has Profile', [
                'investor' => 'Has Investor Profile',
                'owner' => 'Has Owner Profile',
                'both' => 'Has Both Profiles',
                'none' => 'No Profiles'
            ], function ($query, $value) {
                switch ($value) {
                    case 'investor':
                        $query->whereHas('investorProfile')->whereDoesntHave('ownerProfile');
                        break;
                    case 'owner':
                        $query->whereHas('ownerProfile')->whereDoesntHave('investorProfile');
                        break;
                    case 'both':
                        $query->whereHas('investorProfile')->whereHas('ownerProfile');
                        break;
                    case 'none':
                        $query->whereDoesntHave('investorProfile')->whereDoesntHave('ownerProfile');
                        break;
                }
            }),

            // Example: Boolean filter
            // 'is_registered' => Filter::boolean('Is Registered'),

            // Example: Number filter
            // 'id' => Filter::number('User ID', 1),

            // Example: Range filter
            // 'id' => Filter::range('User ID Range', 1, 1000),
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

            // User Profiles Column (All profiles user has)
            ->addColumn('user_profiles', function ($model) {
                $hasInvestor = $model->investorProfile !== null;
                $hasOwner = $model->ownerProfile !== null;

                if (!$hasInvestor && !$hasOwner) {
                    return '<span class="badge badge-light-secondary">No Profiles</span>';
                }

                $badges = '';

                if ($hasInvestor) {
                    $badges .= '<span class="badge badge-light-primary me-1 mb-1">
                        <i class="ki-outline ki-chart-line-up fs-7 me-1"></i>
                        Investor
                    </span>';
                }

                if ($hasOwner) {
                    $badges .= '<span class="badge badge-light-info mb-1">
                        <i class="ki-outline ki-briefcase fs-7 me-1"></i>
                        Owner
                    </span>';
                }

                return '<div class="d-flex flex-wrap gap-1">' . $badges . '</div>';
            })

            // Wallet Balance Column with Link to Transactions
            ->addColumn('wallet_balance', function ($model) {
                $hasInvestor = $model->investorProfile !== null;
                $hasOwner = $model->ownerProfile !== null;
                $investorBalance = $hasInvestor ? $model->investorProfile->getWalletBalance() : 0;
                $ownerBalance = $hasOwner ? $model->ownerProfile->getWalletBalance() : 0;
                $totalBalance = $investorBalance + $ownerBalance;

                // Get transaction count for this user
                $transactionCount = \App\Models\Transaction::where(function($q) use ($model, $hasInvestor, $hasOwner) {
                    $q->where('payable_type', 'App\\Models\\User')
                      ->where('payable_id', $model->id);

                    if ($hasInvestor) {
                        $q->orWhere(function($subQ) use ($model) {
                            $subQ->where('payable_type', 'App\\Models\\InvestorProfile')
                                 ->where('payable_id', $model->investorProfile->id);
                        });
                    }

                    if ($hasOwner) {
                        $q->orWhere(function($subQ) use ($model) {
                            $subQ->where('payable_type', 'App\\Models\\OwnerProfile')
                                 ->where('payable_id', $model->ownerProfile->id);
                        });
                    }
                })->count();

                if (!$hasInvestor && !$hasOwner) {
                    return '<span class="text-muted fst-italic">No wallet</span>';
                }

                $transactionsUrl = route('admin.transactions.by-user', $model->id);

                return '
                    <div class="d-flex flex-column">
                        <a href="' . $transactionsUrl . '"
                           class="text-success fw-bold text-hover-primary fs-6"
                           data-bs-toggle="tooltip"
                           title="Click to view all transactions">
                            <i class="ki-outline ki-wallet fs-5 me-1"></i>
                            ' . number_format($totalBalance, 2) . ' SAR
                        </a>
                        <div class="text-muted fs-8 mt-1">
                            ' . ($hasInvestor ? '<span class="badge badge-light-success fs-9 me-1">Investor: ' . number_format($investorBalance, 2) . '</span>' : '') . '
                            ' . ($hasOwner ? '<span class="badge badge-light-info fs-9">Owner: ' . number_format($ownerBalance, 2) . '</span>' : '') . '
                        </div>
                        ' . ($transactionCount > 0 ? '<div class="text-muted fs-9 mt-1">' . $transactionCount . ' transaction' . ($transactionCount > 1 ? 's' : '') . '</div>' : '') . '
                    </div>
                ';
            })

            ->editColumn('is_active', function ($model) {
                return $model->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->editColumn('active_profile_type', function ($model) {
                if (!$model->active_profile_type) {
                    return '<span class="badge badge-light-secondary" data-bs-toggle="tooltip" title="User has not selected an active profile">
                        <i class="ki-outline ki-cross-circle fs-7 me-1"></i>
                        None
                    </span>';
                }

                $profileIcon = match ($model->active_profile_type) {
                    'investor' => 'ki-chart-line-up',
                    'owner' => 'ki-briefcase',
                    default => 'ki-profile-circle',
                };

                $profileColor = match ($model->active_profile_type) {
                    'investor' => 'primary',
                    'owner' => 'info',
                    default => 'secondary',
                };

                $profileLabel = match ($model->active_profile_type) {
                    'investor' => 'Investor',
                    'owner' => 'Owner',
                    default => 'Unknown',
                };

                return '<div data-bs-toggle="tooltip" title="Last active profile selected by user">
                    <span class="badge badge-' . $profileColor . '">
                        <i class="ki-outline ' . $profileIcon . ' fs-7 me-1"></i>
                        ' . $profileLabel . '
                    </span>
                    <i class="ki-outline ki-information-5 fs-8 text-muted ms-1"></i>
                </div>';
            })
            ->addColumn('display_name', function ($model) {
                return $model->display_name;
            })
            ->editColumn('created_at', function ($model) {
                return $model->created_at->format('Y-m-d H:i:s');
            })
            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query); // Auto-apply all filters
            },true)
            ->rawColumns(['action', 'user_profiles', 'wallet_balance', 'is_active', 'active_profile_type', 'display_name'])
            ->make(true);
    }
}
