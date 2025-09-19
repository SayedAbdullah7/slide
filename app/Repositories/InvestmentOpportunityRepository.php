<?php

namespace App\Repositories;

use App\Models\InvestmentOpportunity;

class InvestmentOpportunityRepository
{
    protected function baseQuery()
    {
        return InvestmentOpportunity::with(['category', 'ownerProfile']);
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

    public function getAvailable($perPage, $page)
    {
        return $this->baseQuery()
            ->activeAndVisible()
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'available_page', $page);
    }

    public function getComing($perPage, $page)
    {
        return $this->baseQuery()
            ->coming()
            ->orderBy('offering_start_date', 'asc')
            ->paginate($perPage, ['*'], 'coming_page', $page);
    }

    public function getMy($userId, $perPage, $page)
    {
        return $this->baseQuery()
            ->ownedBy($userId)
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'my_page', $page);
    }

    public function getClosed($perPage, $page)
    {
        return $this->baseQuery()
            ->closed()
            ->orderBy('offering_end_date', 'desc')
            ->paginate($perPage, ['*'], 'closed_page', $page);
    }
}
