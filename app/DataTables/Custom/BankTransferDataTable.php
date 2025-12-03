<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\BankTransferRequest;
use Illuminate\Support\Str;

class BankTransferDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     * These columns will be searched in related models.
     */
    protected array $searchableRelations = [
        'user' => ['email', 'phone', 'display_name'],
        'investor' => ['full_name', 'national_id'],
        'bank' => ['name_ar', 'name_en', 'code'],
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
            Column::create('transfer_reference')->setTitle('Reference')->setSearchable(true),
            Column::create('user_info')->setTitle('User')->setSearchable(false)->setOrderable(false),
            Column::create('amount')->setTitle('Amount (SAR)')->setSearchable(false),
            Column::create('status')->setTitle('Status')->setSearchable(true),
            Column::create('bank_info')->setTitle('Bank')->setSearchable(false)->setOrderable(false),
            Column::create('receipt_info')->setTitle('Receipt')->setSearchable(false)->setOrderable(false),
            Column::create('admin_notes')->setTitle('Admin Notes')->setVisible(true),
            Column::create('rejection_reason')->setTitle('Rejection Reason')->setVisible(false),
            Column::create('action_by_info')->setTitle('Action By')->setSearchable(false)->setOrderable(false)->setVisible(false),
            Column::create('processed_at')->setTitle('Processed Date')->setOrderable(false)->setVisible(false),
            Column::create('created_at')->setTitle('Request Date'),
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
                BankTransferRequest::STATUS_PENDING => 'Pending',
                BankTransferRequest::STATUS_APPROVED => 'Approved',
                BankTransferRequest::STATUS_REJECTED => 'Rejected',
            ]),
            'amount_range' => Filter::select('Amount Range', [
                '0-1000' => '0 - 1,000 SAR',
                '1000-5000' => '1,000 - 5,000 SAR',
                '5000-10000' => '5,000 - 10,000 SAR',
                '10000-50000' => '10,000 - 50,000 SAR',
                '50000+' => '50,000+ SAR',
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
        $query = BankTransferRequest::with(['user', 'investor', 'bank', 'actionBy']);

        return DataTables::of($query)
            // Transfer Reference Number
            ->editColumn('transfer_reference', function ($model) {
                if (!$model->transfer_reference) {
                    return '<span class="badge badge-light-warning">Not Set</span>';
                }
                return '
                    <div class="d-flex align-items-center">
                        <code class="fs-7 fw-bold text-gray-800">' . e($model->transfer_reference) . '</code>
                        <button class="btn btn-sm btn-icon btn-light-primary ms-2"
                                onclick="navigator.clipboard.writeText(\'' . $model->transfer_reference . '\')"
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

                return '
                    <div class="d-flex flex-column">
                        <a href="' . route('admin.users.show', $model->user_id) . '"
                           class="text-gray-800 fw-bold text-hover-primary mb-1">
                            ' . e($name) . '
                        </a>
                        <div class="text-muted fs-8">
                            <div><i class="ki-outline ki-sms fs-7 me-1"></i>' . e($email) . '</div>
                        </div>
                    </div>
                ';
            })

            // Amount
            ->editColumn('amount', function ($model) {
                if (!$model->amount) {
                    return '<span class="text-muted">Not set</span>';
                }

                $formattedAmount = number_format($model->amount, 2);

                return '
                    <div class="d-flex flex-column">
                        <span class="text-primary fw-bold fs-6">+' . $formattedAmount . ' SAR</span>
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
                if ($model->bank) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 fw-bold">' . e($model->bank->name_ar) . '</span>
                        </div>
                    ';
                }
                return '<span class="text-muted">N/A</span>';
            })

            // Receipt Information
            ->addColumn('receipt_info', function ($model) {
                if (!$model->receipt_file) {
                    return '<span class="text-muted">No receipt</span>';
                }

                $fileUrl = $model->receipt_url;
                $fileName = $model->receipt_file_name ?? 'View Receipt';
                $fileType = $model->receipt_type;

                if ($fileType === 'image') {
                    return '
                        <div class="d-flex flex-column">
                            <a href="' . $fileUrl . '" target="_blank" class="text-primary fw-bold">
                                <i class="ki-outline ki-file fs-5 me-1"></i>
                                ' . e($fileName) . '
                            </a>
                            <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-light-primary mt-1">
                                <i class="ki-outline ki-eye fs-6 me-1"></i>
                                View Image
                            </a>
                        </div>
                    ';
                } elseif ($fileType === 'pdf') {
                    return '
                        <div class="d-flex flex-column">
                            <a href="' . $fileUrl . '" target="_blank" class="text-primary fw-bold">
                                <i class="ki-outline ki-file fs-5 me-1"></i>
                                ' . e($fileName) . '
                            </a>
                            <a href="' . $fileUrl . '" target="_blank" class="btn btn-sm btn-light-primary mt-1">
                                <i class="ki-outline ki-file-pdf fs-6 me-1"></i>
                                View PDF
                            </a>
                        </div>
                    ';
                }

                return '
                    <a href="' . $fileUrl . '" target="_blank" class="text-primary">
                        <i class="ki-outline ki-file fs-5 me-1"></i>
                        ' . e($fileName) . '
                    </a>
                ';
            })

            // Admin Notes
            ->editColumn('admin_notes', function ($model) {
                if (!$model->admin_notes) {
                    return '<span class="text-muted">-</span>';
                }
                return '<div class="text-wrap" style="max-width: 200px;">' . e(Str::limit($model->admin_notes, 100)) . '</div>';
            })

            // Rejection Reason
            ->editColumn('rejection_reason', function ($model) {
                if (!$model->rejection_reason) {
                    return '<span class="text-muted">-</span>';
                }
                return '<div class="text-wrap" style="max-width: 200px;">' . e(Str::limit($model->rejection_reason, 100)) . '</div>';
            })

            // Action By
            ->addColumn('action_by_info', function ($model) {
                if (!$model->actionBy) {
                    return '<span class="text-muted">N/A</span>';
                }

                return '
                    <div class="d-flex flex-column">
                        <span class="text-gray-800">' . e($model->actionBy->name ?? 'Unknown Admin') . '</span>
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

            // Actions
            ->addColumn('action', function ($model) {
                return view('pages.bank-transfer.columns._actions', compact('model'))->render();
            })

            ->filter(function ($query) {
                // Apply search on relations
                $this->applySearch($query);

                // Apply status filter
                if (request()->has('filter_status') && request()->filter_status !== '' && request()->filter_status !== null) {
                    $query->where('status', request()->filter_status);
                }

                // Apply amount range filter
                if (request()->has('filter_amount_range') && request()->filter_amount_range !== '' && request()->filter_amount_range !== null) {
                    $this->applyAmountRangeFilter($query, request()->filter_amount_range);
                }

                // Apply date filter
                if (request()->has('filter_created_at') && request()->filter_created_at !== '' && request()->filter_created_at !== null) {
                    $query->whereDate('created_at', request()->filter_created_at);
                }
            }, true)

            ->rawColumns([
                'transfer_reference', 'user_info', 'amount', 'status', 'bank_info',
                'receipt_info', 'admin_notes', 'rejection_reason', 'action_by_info', 'processed_at', 'created_at', 'action'
            ])

            ->make(true);
    }

    /**
     * Get status configuration for displaying
     */
    private function getStatusConfig(string $status): array
    {
        return match ($status) {
            BankTransferRequest::STATUS_PENDING => [
                'color' => 'warning',
                'icon' => 'ki-time',
                'label' => 'Pending',
            ],
            BankTransferRequest::STATUS_APPROVED => [
                'color' => 'success',
                'icon' => 'ki-check-circle',
                'label' => 'Approved',
            ],
            BankTransferRequest::STATUS_REJECTED => [
                'color' => 'danger',
                'icon' => 'ki-cross-circle',
                'label' => 'Rejected',
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

