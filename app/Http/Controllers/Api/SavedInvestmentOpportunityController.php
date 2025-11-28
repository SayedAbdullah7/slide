<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunity;
use App\Models\SavedInvestmentOpportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SavedInvestmentOpportunityController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all saved investment opportunities for the authenticated investor
     */
    public function index(Request $request)
    {
        try {
            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);

            $savedOpportunities = SavedInvestmentOpportunity::with([
                'investmentOpportunity' => function ($query) {
                    $query->with(['category', 'ownerProfile.user', 'attachments', 'guarantees']);
                }
            ])
            ->where('investor_profile_id', $investor->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

            return $this->respondSuccessWithData('Saved opportunities retrieved successfully', $savedOpportunities);
        } catch (\Exception $e) {
            return $this->respondError('Failed to retrieve saved opportunities: ' . $e->getMessage());
        }
    }

    /**
     * Save an investment opportunity
     */
    public function store(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'investment_opportunity_id' => 'required|exists:investment_opportunities,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $investmentOpportunityId = $request->investment_opportunity_id;

            // Check if already saved
            $existingSave = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)
                ->where('investment_opportunity_id', $investmentOpportunityId)
                ->first();

            if ($existingSave) {
                return $this->respondError('Investment opportunity already saved');
            }

            $savedOpportunity = SavedInvestmentOpportunity::create([
                'investor_profile_id' => $investor->id,
                'investment_opportunity_id' => $investmentOpportunityId,
            ]);

            $savedOpportunity->load(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile.user']);

            return $this->respondSuccessWithData('Investment opportunity saved successfully', $savedOpportunity);
        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e->validator);
        } catch (\Exception $e) {
            return $this->respondError('Failed to save investment opportunity: ' . $e->getMessage());
        }
    }

    /**
     * Remove a saved investment opportunity
     */
    public function destroy(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'investment_opportunity_id' => 'required|exists:investment_opportunities,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $investmentOpportunityId = $request->investment_opportunity_id;

            $deleted = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)
                ->where('investment_opportunity_id', $investmentOpportunityId)
                ->delete();

            if ($deleted) {
                return $this->respondSuccess('Investment opportunity removed from saved list');
            } else {
                return $this->respondError('Saved investment opportunity not found', 404);
            }
        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e->validator);
        } catch (\Exception $e) {
            return $this->respondError('Failed to remove saved investment opportunity: ' . $e->getMessage());
        }
    }

    /**
     * Toggle save status of an investment opportunity
     */
    public function toggle(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'investment_opportunity_id' => 'required|exists:investment_opportunities,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $investmentOpportunityId = $request->investment_opportunity_id;

            $existingSave = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)
                ->where('investment_opportunity_id', $investmentOpportunityId)
                ->first();

            if ($existingSave) {
                $existingSave->delete();
                return $this->respondSuccessWithData('Investment opportunity removed from saved list', [
                    'saved' => false,
                    'investment_opportunity_id' => $investmentOpportunityId
                ]);
            } else {
                $savedOpportunity = SavedInvestmentOpportunity::create([
                    'investor_profile_id' => $investor->id,
                    'investment_opportunity_id' => $investmentOpportunityId,
                ]);

                return $this->respondSuccessWithData('Investment opportunity saved successfully', [
                    'saved' => true,
                    'investment_opportunity_id' => $investmentOpportunityId,
                    'saved_at' => $savedOpportunity->created_at
                ]);
            }
        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e->validator);
        } catch (\Exception $e) {
            return $this->respondError('Failed to toggle save status: ' . $e->getMessage());
        }
    }

    /**
     * Check if investment opportunities are saved
     */
    public function checkStatus(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'investment_opportunity_ids' => 'required|array',
                'investment_opportunity_ids.*' => 'exists:investment_opportunities,id'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $opportunityIds = $request->investment_opportunity_ids;

            $savedIds = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)
                ->whereIn('investment_opportunity_id', $opportunityIds)
                ->pluck('investment_opportunity_id')
                ->toArray();

            $statusMap = [];
            foreach ($opportunityIds as $id) {
                $statusMap[$id] = in_array($id, $savedIds);
            }

            return $this->respondSuccessWithData('Save status retrieved successfully', [
                'saved_status' => $statusMap
            ]);
        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e->validator);
        } catch (\Exception $e) {
            return $this->respondError('Failed to check save status: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics about saved opportunities
     */
    public function stats()
    {
        try {
            $investor = Auth::user()->investorProfile;

            if (!$investor) {
                return $this->respondError('Investor profile not found', 404);
            }

            $totalSaved = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)->count();

            $savedByStatus = SavedInvestmentOpportunity::where('investor_profile_id', $investor->id)
                ->join('investment_opportunities', 'saved_investment_opportunities.investment_opportunity_id', '=', 'investment_opportunities.id')
                ->selectRaw('investment_opportunities.status, COUNT(*) as count')
                ->groupBy('investment_opportunities.status')
                ->pluck('count', 'status')
                ->toArray();

            return $this->respondSuccessWithData('Save statistics retrieved successfully', [
                'total_saved' => $totalSaved,
                'saved_by_status' => $savedByStatus
            ]);
        } catch (\Exception $e) {
            return $this->respondError('Failed to retrieve save statistics: ' . $e->getMessage());
        }
    }
}
