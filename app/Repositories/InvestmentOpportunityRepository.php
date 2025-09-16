<?php

namespace App\Repositories;

use App\Models\InvestmentOpportunity;

class InvestmentOpportunityRepository
{
    protected function baseQuery()
    {
        return InvestmentOpportunity::with(['category', 'ownerProfile']);
    }

    public function getAvailable($perPage, $page)
    {
        return $this->baseQuery()
//            ->where('is_fundable', true)
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'available_page', $page);
    }

    public function getComing($perPage, $page)
    {
        return $this->baseQuery()
//            ->whereDate('offering_start_date', '>', now())
            ->orderBy('offering_start_date', 'asc')
            ->paginate($perPage, ['*'], 'coming_page', $page);
    }

    public function getMy($userId, $perPage, $page)
    {
        return $this->baseQuery()
//            ->where('user_id', $userId)
            ->orderBy('offering_start_date', 'desc')
            ->paginate($perPage, ['*'], 'my_page', $page);
    }

    public function getClosed($perPage, $page)
    {
        return $this->baseQuery()
            ->whereDate('offering_end_date', '<', now())
            ->orderBy('offering_end_date', 'desc')
            ->paginate($perPage, ['*'], 'closed_page', $page);
    }
}
