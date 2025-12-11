<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\UserDataTable;
use App\Models\User;
use App\Services\WalletService;
use App\WalletDepositSourceEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(UserDataTable $dataTable, Request $request): JsonResponse|View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        return view('pages.user.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('pages.user.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'is_active' => 'boolean',
            'is_registered' => 'boolean',
        ]);

        $user = User::create($validated);

        return response()->json([
            'status' => true,
            'msg' => 'User created successfully.',
            'data' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $user->load(['surveyAnswers.question', 'surveyAnswers.option', 'investorProfile', 'ownerProfile']);
        return view('pages.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('pages.user.form', ['model' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_active' => 'boolean',
            'is_registered' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'User updated successfully.',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        return response()->json([
            'status' => false,
            'msg' => 'User cannot be deleted.'
        ]);
        $user->delete();

        return response()->json([
            'status' => true,
            'msg' => 'User deleted successfully.'
        ]);
    }

    /**
     * Show the form for creating an investor profile.
     */
    public function createInvestorProfile(User $user): View
    {
        return view('pages.user.profiles.investor-form', compact('user'));
    }

    /**
     * Store a newly created investor profile.
     */
    public function storeInvestorProfile(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|max:50',
            'birth_date' => 'required|date|before:today',
            'extra_data' => 'nullable|string',
        ]);

        $investorProfile = $user->investorProfile()->create($validated);
        $user->update(['active_profile_type' => User::PROFILE_INVESTOR]);

        return response()->json([
            'status' => true,
            'msg' => 'Investor profile created successfully.',
            'data' => $investorProfile
        ]);
    }

    /**
     * Show the form for creating an owner profile.
     */
    public function createOwnerProfile(User $user): View
    {
        return view('pages.user.profiles.owner-form', compact('user'));
    }

    /**
     * Store a newly created owner profile.
     */
    public function storeOwnerProfile(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string',
            'business_phone' => 'nullable|string',
            'business_email' => 'nullable|email',
            'business_website' => 'nullable|url',
            'business_description' => 'nullable|string',
            'tax_number' => 'required|string|max:50|unique:owner_profiles,tax_number',
            'goal' => 'nullable|string',
        ]);

        $ownerProfile = $user->ownerProfile()->create($validated);
        $user->update(['active_profile_type' => User::PROFILE_OWNER]);

        return response()->json([
            'status' => true,
            'msg' => 'Owner profile created successfully.',
            'data' => $ownerProfile
        ]);
    }

    /**
     * Show the form for editing an investor profile.
     */
    public function editInvestorProfile(User $user): View
    {
        $investorProfile = $user->investorProfile;
        return view('pages.user.profiles.investor-form', compact('user', 'investorProfile'));
    }

    /**
     * Update the specified investor profile.
     */
    public function updateInvestorProfile(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|max:50',
            'birth_date' => 'required|date|before:today',
            'extra_data' => 'nullable|string',
        ]);

        $user->investorProfile()->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Investor profile updated successfully.'
        ]);
    }

    /**
     * Show the form for editing an owner profile.
     */
    public function editOwnerProfile(User $user): View
    {
        $ownerProfile = $user->ownerProfile;
        return view('pages.user.profiles.owner-form', compact('user', 'ownerProfile'));
    }

    /**
     * Update the specified owner profile.
     */
    public function updateOwnerProfile(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_address' => 'nullable|string',
            'business_phone' => 'nullable|string',
            'business_email' => 'nullable|email',
            'business_website' => 'nullable|url',
            'business_description' => 'nullable|string',
            'tax_number' => 'required|string|max:50|unique:owner_profiles,tax_number,' . $user->ownerProfile->id,
            'goal' => 'nullable|string',
        ]);

        $user->ownerProfile()->update($validated);

        return response()->json([
            'status' => true,
            'msg' => 'Owner profile updated successfully.'
        ]);
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $user->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Verify user email manually
     */
    public function verifyEmail(User $user): JsonResponse
    {
        $user->update(['email_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully'
        ]);
    }

    /**
     * Verify user phone manually
     */
    public function verifyPhone(User $user): JsonResponse
    {
        $user->update(['phone_verified_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Phone verified successfully'
        ]);
    }

    /**
     * Show deposit form
     */
    public function showDepositForm(User $user): View
    {
        $user->load(['investorProfile', 'ownerProfile']);
        $hasInvestor = $user->investorProfile !== null;
        $hasOwner = $user->ownerProfile !== null;
        $investorBalance = $hasInvestor ? $this->walletService->getWalletBalance($user->investorProfile) : 0;
        $ownerBalance = $hasOwner ? $this->walletService->getWalletBalance($user->ownerProfile) : 0;

        return view('pages.user.forms.wallet-operation', compact(
            'user', 'hasInvestor', 'hasOwner', 'investorBalance', 'ownerBalance'
        ))->with('type', 'deposit');
    }

    /**
     * Show withdraw form
     */
    public function showWithdrawForm(User $user): View
    {
        $user->load(['investorProfile', 'ownerProfile']);
        $hasInvestor = $user->investorProfile !== null;
        $hasOwner = $user->ownerProfile !== null;
        $investorBalance = $hasInvestor ? $this->walletService->getWalletBalance($user->investorProfile) : 0;
        $ownerBalance = $hasOwner ? $this->walletService->getWalletBalance($user->ownerProfile) : 0;

        return view('pages.user.forms.wallet-operation', compact(
            'user', 'hasInvestor', 'hasOwner', 'investorBalance', 'ownerBalance'
        ))->with('type', 'withdraw');
    }

    /**
     * Deposit balance to user wallet
     */
    public function deposit(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'wallet_type' => 'required|in:investor,owner',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            // Get the appropriate wallet
            if ($validated['wallet_type'] === 'investor') {
                if (!$user->investorProfile) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'User does not have an investor profile'
                    ], 400);
                }
                $wallet = $user->investorProfile;
            } else {
                // now owner profile not support the wallet
                return response()->json([
                    'status' => false,
                    'msg' => 'Owner profile is not supported the wallet'
                ], 400);
                if (!$user->ownerProfile) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'User does not have an owner profile'
                    ], 400);
                }
                $wallet = $user->ownerProfile;
            }

            // Deposit to wallet using WalletService
            $this->walletService->depositToWallet($wallet, $validated['amount'], [
                'source' => WalletDepositSourceEnum::DASHBOARD,
                'description' => $validated['description'] ?? 'Admin deposit',
                'admin_user_id' => Auth::id(),
                'transaction_date' => now()->toDateTimeString()
            ]);

            $newBalance = $this->walletService->getWalletBalance($wallet);

            return response()->json([
                'status' => true,
                'msg' => "Successfully deposited {$validated['amount']} SAR. New balance: " . number_format($newBalance, 2) . " SAR",
                'reload' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Error depositing balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Withdraw balance from user wallet
     */
    public function withdraw(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'wallet_type' => 'required|in:investor,owner',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            // Get the appropriate wallet
            if ($validated['wallet_type'] === 'investor') {
                if (!$user->investorProfile) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'User does not have an investor profile'
                    ], 400);
                }
                $wallet = $user->investorProfile;
            } else {
                if (!$user->ownerProfile) {
                    return response()->json([
                        'status' => false,
                        'msg' => 'User does not have an owner profile'
                    ], 400);
                }
                $wallet = $user->ownerProfile;
            }

            // Check if wallet has sufficient balance using WalletService
            $currentBalance = $this->walletService->getWalletBalance($wallet);
            if ($currentBalance < $validated['amount']) {
                return response()->json([
                    'status' => false,
                    'msg' => "Insufficient balance. Available: " . number_format($currentBalance, 2) . " SAR, Requested: " . number_format($validated['amount'], 2) . " SAR"
                ], 400);
            }

            // Withdraw from wallet using WalletService
            $this->walletService->withdrawFromWallet($wallet, $validated['amount'], [
                'description' => $validated['description'] ?? 'Admin withdrawal',
                'admin_user_id' => Auth::id(),
                'transaction_date' => now()->toDateTimeString()
            ]);

            $newBalance = $this->walletService->getWalletBalance($wallet);

            return response()->json([
                'status' => true,
                'msg' => "Successfully withdrew {$validated['amount']} SAR. New balance: " . number_format($newBalance, 2) . " SAR",
                'reload' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'msg' => 'Error withdrawing balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
