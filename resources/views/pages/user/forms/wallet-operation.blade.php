@props([
    'user',
    'type' => 'deposit', // 'deposit' or 'withdraw'
    'hasInvestor' => false,
    'hasOwner' => false,
    'investorBalance' => 0,
    'ownerBalance' => 0,
])

@php
    $isDeposit = $type === 'deposit';
    $isWithdraw = $type === 'withdraw';
    $color = $isDeposit ? 'success' : 'warning';
    $icon = $isDeposit ? 'ki-arrow-down' : 'ki-arrow-up';
    $iconPrefix = $isDeposit ? 'ki-plus' : 'ki-minus';
    $title = $isDeposit ? 'Deposit Balance' : 'Withdraw Balance';
    $action = route($isDeposit ? 'admin.users.deposit' : 'admin.users.withdraw', $user->id);
@endphp

<div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
     data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
     data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    <!-- Header Info -->
    <div class="alert alert-{{ $color }} d-flex align-items-center mb-7">
        <div class="symbol symbol-circle symbol-50px bg-light-{{ $color }} me-4">
            <i class="ki-outline {{ $icon }} fs-2x text-{{ $color }}"></i>
        </div>
        <div class="flex-grow-1">
            <h4 class="mb-1 text-{{ $color }}">{{ $title }}</h4>
            <span class="text-gray-700">{{ $user->display_name }}</span>
        </div>
    </div>

    <!-- Form -->
    <form id="kt_modal_form" action="{{ $action }}" data-method="POST">
        @csrf

        <div class="row">
            <!-- Wallet Selection -->
            <div class="col-md-6 mb-7">
                <label class="form-label fw-bold required fs-5">Select Wallet</label>
                <select class="form-select form-select-lg"
                        name="wallet_type"
                        @if($isWithdraw) id="wallet_select_{{ $user->id }}" onchange="updateMaxAmount({{ $user->id }})" @endif
                        required>
                    <option value="">Choose wallet...</option>
                    @if($hasInvestor)
                        <option value="investor" data-balance="{{ $investorBalance }}">
                            💼 Investor Wallet ({{ number_format($investorBalance, 2) }} SAR)
                        </option>
                    @endif
                    @if($hasOwner)
                        <option value="owner" data-balance="{{ $ownerBalance }}">
                            🏢 Owner Wallet ({{ number_format($ownerBalance, 2) }} SAR)
                        </option>
                    @endif
                </select>
                <div class="form-text">Choose which wallet to {{ $isDeposit ? 'deposit into' : 'withdraw from' }}</div>
            </div>

            <!-- Amount Input -->
            <div class="col-md-6 mb-7">
                <label class="form-label fw-bold required fs-5">Amount (SAR)</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-{{ $color }} text-white">
                        <i class="ki-outline text-white {{ $iconPrefix }} fs-3"></i>
                    </span>
                    <input type="number"
                           class="form-control form-control-lg"
                           name="amount"
                           @if($isWithdraw) id="amount_input_{{ $user->id }}" @endif
                           placeholder="0.00"
                           step="0.01"
                           min="0.01"
                           required
                           autofocus>
                    <span class="input-group-text">SAR</span>
                </div>
                @if($isWithdraw)
                    <div class="form-text">
                        Maximum: <span id="max_amount_{{ $user->id }}" class="fw-bold text-{{ $color }}">Select a wallet</span>
                    </div>
                @else
                    <div class="form-text">Minimum: 0.01 SAR</div>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="mb-7">
            <label class="form-label fw-bold fs-5">Description (Optional)</label>
            <textarea class="form-control"
                      name="description"
                      rows="3"
                      placeholder="e.g., {{ $isDeposit ? 'Initial deposit, Investment return, Refund' : 'Withdrawal request, Payment, Expense' }}"></textarea>
            <div class="form-text">Add a note about this {{ $type }} for your records</div>
        </div>

        <!-- Current Balance Display -->
        <div class="border border-{{ $color }} border-dashed rounded p-5 bg-light-{{ $color }} mb-7">
            <div class="row">
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <div class="text-muted fw-semibold fs-7 mb-2">Current Total</div>
                    <div class="text-gray-800 fw-bold fs-3">{{ number_format($investorBalance + $ownerBalance, 2) }} SAR</div>
                </div>
                @if($hasInvestor)
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <div class="text-muted fw-semibold fs-7 mb-2">Investor Wallet</div>
                        <div class="text-primary fw-bold fs-4">{{ number_format($investorBalance, 2) }} SAR</div>
                    </div>
                @endif
                @if($hasOwner)
                    <div class="col-md-4 text-center">
                        <div class="text-muted fw-semibold fs-7 mb-2">Owner Wallet</div>
                        <div class="text-info fw-bold fs-4">{{ number_format($ownerBalance, 2) }} SAR</div>
                    </div>
                @endif
            </div>
        </div>

        @if($isWithdraw)
            <!-- Warning Alert for Withdrawal -->
            <div class="alert alert-warning d-flex align-items-center">
                <i class="ki-outline ki-shield-cross fs-2x me-3"></i>
                <div>
                    <div class="fw-bold mb-1">Balance Check Required</div>
                    <div class="fs-7">Amount must not exceed available wallet balance</div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="text-center pt-5">
            <button type="button" class="btn btn-light me-3 close" data-bs-dismiss="modal">
                <i class="ki-outline ki-cross fs-4"></i>
                Cancel
            </button>
            <button type="submit" class="btn btn-{{ $color }}">
                <i class="ki-outline ki-check fs-4 me-1"></i>
                Confirm {{ ucfirst($type) }}
            </button>
        </div>
    </form>
</div>

@if($isWithdraw)
<script>
function updateMaxAmount(userId) {
    const select = document.getElementById(`wallet_select_${userId}`);
    const maxDisplay = document.getElementById(`max_amount_${userId}`);
    const amountInput = document.getElementById(`amount_input_${userId}`);

    if (select && select.selectedIndex > 0) {
        const balance = parseFloat(select.options[select.selectedIndex].dataset.balance);
        maxDisplay.textContent = `${balance.toFixed(2)} SAR`;
        amountInput.max = balance;

        // Add withdrawal validation
        amountInput.addEventListener('input', function() {
            if (parseFloat(this.value) > balance) {
                this.setCustomValidity(`Amount exceeds available balance (${balance.toFixed(2)} SAR)`);
            } else {
                this.setCustomValidity('');
            }
        });
    } else {
        maxDisplay.textContent = 'Select a wallet';
        amountInput.max = '';
    }
}
</script>
@endif

