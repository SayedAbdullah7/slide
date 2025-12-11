<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Transaction;
use App\Models\User;
use App\Models\InvestorProfile;
use App\Models\OwnerProfile;

class TransactionDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        'payable' => ['full_name', 'email', 'business_name'], // Search in user/profiles
    ];

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id')->setTitle('ID')->setSearchable(true),
            Column::create('payable_info')->setTitle('Account Holder')->setSearchable(false)->setOrderable(false),
            Column::create('type')->setTitle('Type')->setSearchable(false),
            Column::create('amount')->setTitle('Amount (SAR)')->setSearchable(false),
            // Column::create('confirmed')->setTitle('Status')->setSearchable(false),
            Column::create('balance_after')->setTitle('Balance After')->setSearchable(false)->setOrderable(false)->setVisible(false),
            Column::create('description')->setTitle('Description')->setSearchable(false)->setOrderable(false),
            Column::create('wallet_id')->setTitle('Wallet ID')->setSearchable(false)->setVisible(false),
            Column::create('uuid')->setTitle('UUID')->setSearchable(false)->setVisible(false),
            Column::create('meta')->setTitle('Meta')->setSearchable(false)->setOrderable(false)->setVisible(false),
            Column::create('created_at')->setTitle('Date')->setOrderable(true),
            Column::create('updated_at')->setTitle('Last Updated')->setOrderable(false)->setVisible(false),
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
            'type' => Filter::select('Transaction Type', [
                'deposit' => 'Deposit',
                'withdraw' => 'Withdraw',
            ]),
            'confirmed' => Filter::select('Status', [
                '1' => 'Confirmed',
                '0' => 'Pending',
            ]),
            'payable_type' => Filter::select('Account Type', [
                'App\Models\User' => 'User',
                'App\Models\InvestorProfile' => 'Investor Profile',
                'App\Models\OwnerProfile' => 'Owner Profile',
            ]),
            'amount_range' => Filter::select('Amount Range', [
                '0-1000' => '0 - 1,000 SAR',
                '1000-5000' => '1,000 - 5,000 SAR',
                '5000-10000' => '5,000 - 10,000 SAR',
                '10000-50000' => '10,000 - 50,000 SAR',
                '50000+' => '50,000+ SAR',
            ]),
            'created_at' => Filter::date('Transaction Date', 'today'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @param int|null $userId Optional user ID to filter transactions
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($userId = null)
    {
        $query = Transaction::with(['payable']);

        // Filter by user if provided
        if ($userId) {
            $query->where(function($q) use ($userId) {
                // Get transactions for the user directly
                $q->where('payable_type', 'App\\Models\\User')
                  ->where('payable_id', $userId);

                // Or get transactions for user's investor profile
                $q->orWhere(function($subQ) use ($userId) {
                    $subQ->where('payable_type', 'App\\Models\\InvestorProfile')
                         ->whereHas('payable', function($profQ) use ($userId) {
                             $profQ->where('user_id', $userId);
                         });
                });

                // Or get transactions for user's owner profile
                $q->orWhere(function($subQ) use ($userId) {
                    $subQ->where('payable_type', 'App\\Models\\OwnerProfile')
                         ->whereHas('payable', function($profQ) use ($userId) {
                             $profQ->where('user_id', $userId);
                         });
                });
            });
        }

        return DataTables::of($query)
            // Payable Information
            ->editColumn('payable_info', function ($model) {
                if (!$model->payable) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $type = class_basename($model->payable_type);
                $icon = $this->getPayableIcon($type);
                $name = $this->getPayableName($model->payable, $type);
                $badgeColor = $this->getPayableBadgeColor($type);

                // Get user ID for filtering link
                $userId = $this->getUserIdFromPayable($model->payable, $type);
                $transactionsUrl = $userId ? route('admin.transactions.by-user', $userId) : '#';

                return '
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ki-outline ' . $icon . ' fs-3 text-' . $badgeColor . ' me-2"></i>
                            <a href="' . $transactionsUrl . '"
                               class="text-gray-800 fw-bold text-hover-primary"
                               data-bs-toggle="tooltip"
                               title="View all transactions for this user">
                                ' . e($name) . '
                            </a>
                        </div>
                        <div class="text-muted fs-8">
                            <span class="badge badge-light-' . $badgeColor . ' fs-9">' . $type . '</span>
                            <span class="text-gray-600 ms-2">ID: ' . $model->payable_id . '</span>
                        </div>
                    </div>
                ';
            })

            // Transaction Type
            ->editColumn('type', function ($model) {
                $color = $model->type === 'deposit' ? 'success' : 'warning';
                $icon = $model->type === 'deposit' ? 'ki-arrow-down' : 'ki-arrow-up';

                return '
                    <span class="badge badge-light-' . $color . '">
                        <i class="ki-outline ' . $icon . ' fs-6 me-1"></i>
                        ' . ucfirst($model->type) . '
                    </span>
                ';
            })

            // Amount
            ->editColumn('amount', function ($model) {
                // Convert amount from cents to SAR
                $amount = $model->amount / 100;
                $color = $model->type === 'deposit' ? 'success' : 'danger';
                $sign = $model->type === 'deposit' ? '+' : '-';

                return '
                    <div class="d-flex flex-column">
                        <span class="text-' . $color . ' fw-bold fs-6">' . $sign . ' ' . number_format($amount, 2) . ' SAR</span>
                        <span class="text-muted fs-8">' . number_format($model->amount) . ' cents</span>
                    </div>
                ';
            })

            // Confirmed Status
            ->editColumn('confirmed', function ($model) {
                if ($model->confirmed) {
                    return '
                        <div class="d-flex align-items-center">
                            <span class="badge badge-light-success">
                                <i class="ki-outline ki-check-circle fs-6 me-1"></i>
                                Confirmed
                            </span>
                        </div>
                    ';
                } else {
                    return '
                        <div class="d-flex align-items-center">
                            <span class="badge badge-light-warning">
                                <i class="ki-outline ki-time fs-6 me-1"></i>
                                Pending
                            </span>
                        </div>
                    ';
                }
            })

            // Balance After Transaction
            ->addColumn('balance_after', function ($model) {
                // Get wallet balance after this transaction using WalletService
                if ($model->payable) {
                    $walletService = app(\App\Services\WalletService::class);
                    $balance = $walletService->getWalletBalance($model->payable);
                    return '<span class="text-gray-800 fw-semibold">' . number_format($balance, 2) . ' SAR</span>';
                }
                return '<span class="text-muted">N/A</span>';
            })

            // Description from Meta
            ->addColumn('description', function ($model) {
                if ($model->meta && isset($model->meta['description'])) {
                    return '<span class="text-gray-700">' . e($model->meta['description']) . '</span>';
                }

                // Generate automatic description
                $description = $model->type === 'deposit'
                    ? 'Wallet deposit'
                    : 'Wallet withdrawal';

                return '<span class="text-muted fst-italic">' . $description . '</span>';
            })

            // UUID
            ->editColumn('uuid', function ($model) {
                return '
                    <div class="d-flex align-items-center">
                        <code class="fs-8 text-gray-600">' . substr($model->uuid, 0, 13) . '...</code>
                        <button class="btn btn-sm btn-icon btn-light-primary ms-2"
                                onclick="navigator.clipboard.writeText(\'' . $model->uuid . '\')"
                                data-bs-toggle="tooltip"
                                title="Copy full UUID">
                            <i class="ki-outline ki-copy fs-6"></i>
                        </button>
                    </div>
                ';
            })

            // Meta Information
            ->editColumn('meta', function ($model) {
                if (!$model->meta || empty($model->meta)) {
                    return '<span class="text-muted">No metadata</span>';
                }

                $metaCount = count($model->meta);
                return '
                    <button class="btn btn-sm btn-light-info"
                            data-bs-toggle="modal"
                            data-bs-target="#metaModal' . $model->id . '">
                        <i class="ki-outline ki-information fs-6 me-1"></i>
                        View (' . $metaCount . ')
                    </button>
                ';
            })

            // Created At
            ->editColumn('created_at', function ($model) {
                if (!$model->created_at) {
                    return '<span class="text-muted">N/A</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold">' . $model->created_at->format('M d, Y') . '</span>
                        <span class="text-muted fs-8">' . $model->created_at->format('h:i A') . '</span>
                        <span class="text-muted fs-9">' . $model->created_at->diffForHumans() . '</span>
                    </div>
                ';
            })

            // Updated At
            ->editColumn('updated_at', function ($model) {
                if (!$model->updated_at) {
                    return '<span class="text-muted">N/A</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800">' . $model->updated_at->format('M d, Y h:i A') . '</span>
                        <span class="text-muted fs-8">' . $model->updated_at->diffForHumans() . '</span>
                    </div>
                ';
            })

            // Actions
            ->addColumn('action', function ($model) {
                return view('pages.transaction.columns._actions', compact('model'))->render();
            })

            ->filter(function ($query) {
                $this->applySearch($query);

                // Apply filters
                $filters = request()->input('filters', []);

                // Transaction Type Filter
                if (!empty($filters['type'])) {
                    $query->where('type', $filters['type']);
                }

                // Confirmed Status Filter
                if (isset($filters['confirmed']) && $filters['confirmed'] !== '') {
                    $query->where('confirmed', (bool) $filters['confirmed']);
                }

                // Payable Type Filter
                if (!empty($filters['payable_type'])) {
                    $query->where('payable_type', $filters['payable_type']);
                }

                // Amount Range Filter
                if (!empty($filters['amount_range'])) {
                    $this->applyAmountRangeFilter($query, $filters['amount_range']);
                }

                // Created At Filter
                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
            },true)
            ->rawColumns(['payable_info', 'type', 'amount', 'confirmed', 'balance_after', 'description', 'uuid', 'meta', 'created_at', 'updated_at', 'action'])
            ->make(true);
    }

    /**
     * Get icon for payable type
     */
    private function getPayableIcon(string $type): string
    {
        return match($type) {
            'User' => 'ki-user',
            'InvestorProfile' => 'ki-chart-line-up',
            'OwnerProfile' => 'ki-briefcase',
            default => 'ki-profile-circle',
        };
    }

    /**
     * Get badge color for payable type
     */
    private function getPayableBadgeColor(string $type): string
    {
        return match($type) {
            'User' => 'primary',
            'InvestorProfile' => 'success',
            'OwnerProfile' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Get displayable name for payable
     */
    private function getPayableName($payable, string $type): string
    {
        if (!$payable) {
            return 'Unknown';
        }

        return match($type) {
            'User' => $payable->full_name ?? $payable->email ?? 'Unknown User',
            'InvestorProfile' => ($payable->user->full_name ?? 'Unknown') . ' (Investor)',
            'OwnerProfile' => $payable->business_name ?? ($payable->user->full_name ?? 'Unknown') . ' (Owner)',
            default => 'Unknown',
        };
    }

    /**
     * Apply amount range filter
     */
    private function applyAmountRangeFilter($query, string $range): void
    {
        switch($range) {
            case '0-1000':
                $query->whereBetween('amount', [0, 100000]); // 0-1000 SAR in cents
                break;
            case '1000-5000':
                $query->whereBetween('amount', [100000, 500000]); // 1000-5000 SAR in cents
                break;
            case '5000-10000':
                $query->whereBetween('amount', [500000, 1000000]); // 5000-10000 SAR in cents
                break;
            case '10000-50000':
                $query->whereBetween('amount', [1000000, 5000000]); // 10000-50000 SAR in cents
                break;
            case '50000+':
                $query->where('amount', '>', 5000000); // 50000+ SAR in cents
                break;
        }
    }

    /**
     * Get user ID from payable (for filtering link)
     */
    private function getUserIdFromPayable($payable, string $type): ?int
    {
        if (!$payable) {
            return null;
        }

        return match($type) {
            'User' => $payable->id,
            'InvestorProfile' => $payable->user_id ?? null,
            'OwnerProfile' => $payable->user_id ?? null,
            default => null,
        };
    }
}
