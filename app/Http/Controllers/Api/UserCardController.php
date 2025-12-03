<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\UserCard;
use App\Models\PaymentLog;
use Google\Cloud\Core\ApiHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserCardController extends Controller
{
    use  ApiResponseTrait;
    /**
     * Get list of user's saved cards
     */
    public function index(): JsonResponse
    {
        try {
            $cards = UserCard::where('user_id', Auth::id())
                ->active()
                ->orderBy('is_default', 'desc')
                ->orderBy('last_used_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($card) {
                    return [
                        'id' => $card->id,
                        'card_display_name' => $card->card_display_name,
                        'card_token' => $card->card_token,
                        'paymob_token_id' => $card->paymob_token_id,
                        'paymob_order_id' => $card->paymob_order_id,
                        'paymob_merchant_id' => $card->paymob_merchant_id,
                        'masked_pan' => $card->masked_pan,
                        // 'last_four' => $card->last_four,
                        'card_brand' => $card->card_brand,
                        'is_default' => $card->is_default,
                        'last_used_at' => $card->last_used_at?->format('Y-m-d H:i:s'),
                        'created_at' => $card->created_at->format('Y-m-d H:i:s'),
                    ];
                });


            return $this->respondSuccessWithData('Cards fetched successfully', $cards);
            return response()->json([
                'success' => true,
                'data' => $cards
            ]);

        } catch (\Exception $e) {
            PaymentLog::error('Error fetching user cards', [
                'user_id' => Auth::id(),
                'exception' => PaymentLog::formatException($e, 2000)
            ], Auth::id(), null, null, 'user_cards_fetch_error');

            return response()->json([
                'success' => false,
                'message' => 'Error fetching cards',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
