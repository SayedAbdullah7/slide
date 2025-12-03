<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Str;

class WithdrawalDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [
        'user' => ['email', 'phone', 'display_name'],
        'investor' => ['full_name', 'national_id'],
        'bankAccount' => ['account_holder_name', 'iban', 'account_number'],
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
            Column::create('reference_number')->setTitle('Reference')->setSearchable(true),
            Column::create('user_info')->setTitle('User')->setSearchable(false)->setOrderable(false),
            Column::create('amount')->setTitle('Amount (SAR)')->setSearchable(false),
            Column::create('status')->setTitle('Status')->setSearchable(true),
            Column::create('bank_info')->setTitle('Bank Account')->setSearchable(true)->setOrderable(false),
            Column::create('admin_notes')->setTitle('Admin Notes')->setVisible(true),
            Column::create('rejection_reason')->setTitle('Rejection Reason')->setVisible(false),
            Column::create('money_withdrawn')->setTitle('Money Withdrawn')->setSearchable(false),
            Column::create('action_by_info')->setTitle('Action By')->setSearchable(false)->setOrderable(false)->setVisible(false),
            Column::create('processed_at')->setTitle('Processed Date')->setOrderable(false)->setVisible(false),
            Column::create('created_at')->setTitle('Request Date'),
            Column::create('completed_at')->setTitle('Completed Date')->setOrderable(false)->setVisible(false),
            // last updated at
            Column::create('updated_at')->setTitle('Last Updated')->setOrderable(true)->setVisible(false),
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
                WithdrawalRequest::STATUS_PENDING => 'Pending',
                WithdrawalRequest::STATUS_PROCESSING => 'Processing',
                WithdrawalRequest::STATUS_COMPLETED => 'Completed',
                WithdrawalRequest::STATUS_REJECTED => 'Rejected',
                WithdrawalRequest::STATUS_CANCELLED => 'Cancelled',
            ]),
            'amount_range' => Filter::select('Amount Range', [
                '0-1000' => '0 - 1,000 SAR',
                '1000-5000' => '1,000 - 5,000 SAR',
                '5000-10000' => '5,000 - 10,000 SAR',
                '10000-50000' => '10,000 - 50,000 SAR',
                '50000+' => '50,000+ SAR',
            ]),
            // money withdrawn
            'money_withdrawn' => Filter::select('Money Withdrawn', [
                '1' => 'Yes',
                '0' => 'No',
            ]),
            'created_at' => Filter::date('Request Date', 'today'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = WithdrawalRequest::with(['user', 'investor', 'bankAccount.bank', 'actionBy']);

        return DataTables::of($query)
            // Reference Number
            ->editColumn('reference_number', function ($model) {
                return '
                    <div class="d-flex align-items-center">
                        <code class="fs-7 fw-bold text-gray-800">' . e($model->reference_number) . '</code>
                        <button class="btn btn-sm btn-icon btn-light-primary ms-2"
                                onclick="navigator.clipboard.writeText(\'' . $model->reference_number . '\')"
                                data-bs-toggle="tooltip"
                                title="Copy Reference Number">
                            <i class="ki-outline ki-copy fs-6"></i>
                        </button>
                    </div>
                ';
            })

            // User Information
            ->addColumn('user_info', function ($model) {
                if (!$model->user) {
                    return '<span class="badge badge-light-secondary">N/A</span>';
                }

                $name = $model->user->display_name ?? 'Unknown User';
                $email = $model->user->email ?? 'N/A';
                $phone = $model->user->phone ?? 'N/A';

                return '
                    <div class="d-flex flex-column">
                        <a href="' . route('admin.users.show', $model->user_id) . '"
                           class="text-gray-800 fw-bold text-hover-primary mb-1">
                            ' . e($name) . '
                        </a>
                        <div class="text-muted fs-8">
                            <div><i class="ki-outline ki-sms fs-7 me-1"></i>' . e($email) . '</div>
                            <div><i class="ki-outline ki-phone fs-7 me-1"></i>' . e($phone) . '</div>
                        </div>
                    </div>
                ';
            })

            // Amount
            ->editColumn('amount', function ($model) {
                $formattedAmount = number_format($model->amount, 2);
                $availableBalance = number_format($model->available_balance, 2);

                return '
                    <div class="d-flex flex-column">
                        <span class="text-danger fw-bold fs-6">-' . $formattedAmount . ' SAR</span>
                        <span class="text-muted fs-8">Available: ' . $availableBalance . ' SAR</span>
                    </div>
                ';
            })

            // Status
            ->editColumn('status', function ($model) {
                $statusConfig = $this->getStatusConfig($model->status);

                return '
                    <span class="badge badge-light-' . $statusConfig['color'] . '">
                        <i class="ki-outline ' . $statusConfig['icon'] . ' fs-6 me-1"></i>
                        ' . $statusConfig['label'] . '
                    </span>
                ';
            })

            // Bank Information
            ->addColumn('bank_info', function ($model) {
                if ($model->bankAccount) {
                    $bank = $model->bankAccount->bank;
                    $bankName = $bank ? $bank->name_ar : 'N/A';
                    $accountNumber = $model->bankAccount->masked_account_number;
                    $holderName = $model->bankAccount->account_holder_name;
                } elseif ($model->bank_details) {
                    $bankName = $model->bank_details['bank_name'] ?? 'N/A';
                    $accountNumber = isset($model->bank_details['masked_account_number'])
                        ? $model->bank_details['masked_account_number']
                        : (isset($model->bank_details['iban']) ? '****' . substr($model->bank_details['iban'], -4) : 'N/A');
                    $holderName = $model->bank_details['account_holder_name'] ?? 'N/A';
                } else {
                    return '<span class="text-muted">No bank details</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-semibold mb-1">' . e($bankName) . '</span>
                        <span class="text-muted fs-8">' . e($holderName) . '</span>
                        <span class="text-muted fs-8"><code>' . e($accountNumber) . '</code></span>
                    </div>
                ';
            })
            // Admin Notes with max limit for 100 characters and warp text
            ->editColumn('admin_notes', function ($model) {
                return '<div class="text-wrap">' . e(Str::limit($model->admin_notes, 100)) . '</div>';
            })
            // Rejection Reason with max limit for 100 characters and warp text
            ->editColumn('rejection_reason', function ($model) {
                return '<div class="text-wrap">' . e(Str::limit($model->rejection_reason, 100)) . '</div>';
            })
            // Money Withdrawn
            ->editColumn('money_withdrawn', function ($model) {
                return '<div class="text-wrap">' . e($model->money_withdrawn ? 'Yes' : 'No') . '</div>';
            })
            // Action By Information
            ->addColumn('action_by_info', function ($model) {
                if (!$model->action_by || !$model->actionBy) {
                    return '<span class="text-muted">No action taken</span>';
                }

                return '
                    <div class="d-flex align-items-center">
                        <span class="text-gray-800 fw-semibold">' . e($model->actionBy->name ?? 'Unknown') . '</span>
                    </div>
                ';
            })

            // Processed At
            ->editColumn('processed_at', function ($model) {
                if (!$model->processed_at) {
                    return '<span class="text-muted">N/A</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800">' . $model->processed_at->format('M d, Y h:i A') . '</span>
                        <span class="text-muted fs-8">' . $model->processed_at->diffForHumans() . '</span>
                    </div>
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

            // Completed At
            ->editColumn('completed_at', function ($model) {
                if (!$model->completed_at) {
                    return '<span class="text-muted">N/A</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800">' . $model->completed_at->format('M d, Y h:i A') . '</span>
                        <span class="text-muted fs-8">' . $model->completed_at->diffForHumans() . '</span>
                    </div>
                ';
            })
            // Last Updated At
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
                return view('pages.withdrawal.columns._actions', compact('model'))->render();
            })

            ->filter(function ($query) {
                $this->applySearch($query);
                $this->applyFilters($query);

                // Apply filters
                $filters = request()->input('filters', []);

                // Status Filter
                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                // Amount Range Filter
                if (!empty($filters['amount_range'])) {
                    $this->applyAmountRangeFilter($query, $filters['amount_range']);
                }

                // Created At Filter
                if (!empty($filters['created_at'])) {
                    $query->whereDate('created_at', $filters['created_at']);
                }
            }, true)
            ->rawColumns(['reference_number', 'user_info', 'amount', 'status', 'bank_info', 'admin_notes', 'rejection_reason', 'money_withdrawn', 'action_by_info', 'processed_at', 'created_at', 'completed_at', 'updated_at', 'action'])
            ->make(true);
    }

    /**
     * Get status configuration
     */
    private function getStatusConfig(string $status): array
    {
        return match ($status) {
            WithdrawalRequest::STATUS_PENDING => [
                'color' => 'warning',
                'icon' => 'ki-time',
                'label' => 'Pending',
            ],
            WithdrawalRequest::STATUS_PROCESSING => [
                'color' => 'info',
                'icon' => 'ki-arrows-circle',
                'label' => 'Processing',
            ],
            WithdrawalRequest::STATUS_COMPLETED => [
                'color' => 'success',
                'icon' => 'ki-check-circle',
                'label' => 'Completed',
            ],
            WithdrawalRequest::STATUS_REJECTED => [
                'color' => 'danger',
                'icon' => 'ki-cross-circle',
                'label' => 'Rejected',
            ],
            WithdrawalRequest::STATUS_CANCELLED => [
                'color' => 'secondary',
                'icon' => 'ki-cross',
                'label' => 'Cancelled',
            ],
            default => [
                'color' => 'secondary',
                'icon' => 'ki-information',
                'label' => ucfirst($status),
            ],
        };
    }

    /**
     * Apply amount range filter
     */
    private function applyAmountRangeFilter($query, string $range): void
    {
        switch ($range) {
            case '0-1000':
                $query->whereBetween('amount', [0, 1000]);
                break;
            case '1000-5000':
                $query->whereBetween('amount', [1000, 5000]);
                break;
            case '5000-10000':
                $query->whereBetween('amount', [5000, 10000]);
                break;
            case '10000-50000':
                $query->whereBetween('amount', [10000, 50000]);
                break;
            case '50000+':
                $query->where('amount', '>', 50000);
                break;
        }
    }
}

