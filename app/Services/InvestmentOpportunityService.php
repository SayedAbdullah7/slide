<?php

namespace App\Services;

use App\Repositories\InvestmentOpportunityRepository;
use App\Http\Resources\InvestmentOpportunityResource;

class InvestmentOpportunityService
{
    protected $repo;

    public function __construct(InvestmentOpportunityRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getHomeData(array $types, array $params, $userId = null)
    {
        $data = [];

        if (in_array('available', $types)) {
            $data['available'] = $this->getPaginatedData(
                'available',
                $params['available_per_page'] ?? 7,
                $params['available_page'] ?? 1
            );
        }

        if (in_array('coming', $types)) {
            $data['coming'] = $this->getPaginatedData(
                'coming',
                $params['coming_per_page'] ?? 7,
                $params['coming_page'] ?? 1
            );
        }

        if (in_array('my', $types)) {
            $data['my'] = $userId
                ? $this->getPaginatedData(
                    'my',
                    $params['my_per_page'] ?? 15,
                    $params['my_page'] ?? 1,
                    $userId
                )
                : $this->getEmptyPagination();
        }

        if (in_array('closed', $types)) {
            $data['closed'] = $this->getPaginatedData(
                'closed',
                $params['closed_per_page'] ?? 3,
                $params['closed_page'] ?? 1
            );
        }
        if (in_array('wallet', $types)) {
            if ($userId) {
                $data['wallet'] = [
                    'balance' => '0',
                    'total_profits' => '0',
                    'pending_profits' => '0',
                ];
            } else {
                $data['wallet'] = [
                    'balance' => '0',
                    'total_profits' => '0',
                    'pending_profits' => '0',
                ];
            }
        }

        return $data;
    }

    protected function getPaginatedData(string $type, int $perPage, int $page, $userId = null)
    {
        $method = 'get' . ucfirst($type);
        $paginator = $userId
            ? $this->repo->{$method}($userId, $perPage, $page)
            : $this->repo->{$method}($perPage, $page);

        return [
            'data' => InvestmentOpportunityResource::collection($paginator->items()),
            'links' => $this->getPaginationLinks($paginator),
            'meta' => $this->getPaginationMeta($paginator)
        ];
    }

    protected function getPaginationLinks($paginator)
    {
        return [
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }

    protected function getPaginationMeta($paginator)
    {
        return [
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'last_page' => $paginator->lastPage(),
            'path' => $paginator->path(),
            'per_page' => $paginator->perPage(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ];
    }

    protected function getEmptyPagination()
    {
        return [
            'data' => [],
            'links' => [
                'first' => null,
                'last' => null,
                'prev' => null,
                'next' => null,
            ],
            'meta' => [
                'current_page' => 1,
                'from' => null,
                'last_page' => 1,
                'path' => null,
                'per_page' => 15,
                'to' => null,
                'total' => 0,
            ]
        ];
    }
}
