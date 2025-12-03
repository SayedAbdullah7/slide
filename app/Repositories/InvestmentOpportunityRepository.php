<?php

namespace App\Repositories;

use App\Models\InvestmentOpportunity;

class InvestmentOpportunityRepository
{
    protected function baseQuery($userId = null)
    {
        $query = InvestmentOpportunity::with(['category', 'ownerProfile']);

        // Eager load saved opportunities for the specific user if provided
        if ($userId) {
            $query->with(['savedOpportunities' => function ($query) use ($userId) {
                $query->whereHas('investorProfile', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            }]);
        }

        return $query;
    }

    /**
     * Search opportunities with filters using scopes
     */
    public function search($filters, $perPage, $page)
    {
        $query = $this->baseQuery();

        // Apply filters using scopes
        if (isset($filters['category_id']) && $filters['category_id']) {
            $query->category($filters['category_id']);
        }

        if (isset($filters['risk_level']) && $filters['risk_level']) {
            $query->riskLevel($filters['risk_level']);
        }

        if (isset($filters['status']) && $filters['status']) {
            $query->status($filters['status']);
        }

        if (isset($filters['min_amount']) || isset($filters['max_amount'])) {
            $query->investmentRange($filters['min_amount'] ?? null, $filters['max_amount'] ?? null);
        }

        if (isset($filters['search']) && $filters['search']) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('location', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAvailable($perPage, $page, $userId = null)
    {
        return $this->baseQuery($userId)
            ->activeAndVisible()
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'available_page', $page);
    }

    public function getComing($perPage, $page, $userId = null)
    {
        return $this->baseQuery($userId)
            ->coming()
            ->orderBy('offering_start_date', 'asc')
            ->paginate($perPage, ['*'], 'coming_page', $page);
    }

    public function getMy($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            ->with(['investments' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->whereHas('investments', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'my_page', $page);
    }

    public function getOwned($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            ->ownedBy($userId)
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'owned_page', $page);
    }

    public function getClosed($perPage, $page, $userId = null)
    {
        return $this->baseQuery($userId)
            ->closed()
            ->orderBy('offering_end_date', 'desc')
            ->paginate($perPage, ['*'], 'closed_page', $page);
    }

    public function getReminders($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            // ->with(['reminders' => function ($query) use ($userId) {
            //     $query->whereHas('investorProfile', function ($q) use ($userId) {
            //         $q->where('user_id', $userId);
            //     });
            // }])
            ->whereHas('reminders', function ($query) use ($userId) {
                $query->whereHas('investorProfile', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->where('is_active', true);
            })
            ->orderBy('offering_start_date', 'asc')
            ->paginate($perPage, ['*'], 'reminders_page', $page);
    }

    public function getSaved($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            ->whereHas('savedOpportunities', function ($query) use ($userId) {
                $query->whereHas('investorProfile', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'saved_page', $page);
    }

    public function getComingSaved($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            ->whereHas('savedOpportunities', function ($query) use ($userId) {
                $query->whereHas('investorProfile', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->coming()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'coming_saved_page', $page);
    }

    public function getAvailableSaved($perPage, $page, $userId)
    {
        return $this->baseQuery($userId)
            ->whereHas('savedOpportunities', function ($query) use ($userId) {
                $query->whereHas('investorProfile', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->open()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'available_saved_page', $page);
    }
}
