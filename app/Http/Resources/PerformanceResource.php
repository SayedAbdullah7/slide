<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_balance' => [
                'amount' => $this->resource['total_balance']['amount'],
                'formatted_amount' => $this->resource['total_balance']['formatted_amount'],
                'currency' => $this->resource['total_balance']['currency'],
            ],
            'general_vision' => [
                'investment' => [
                    'value' => $this->resource['general_vision']['investment']['value'],
                    'formatted' => $this->resource['general_vision']['investment']['formatted'],
                ],
                'realized_profits' => [
                    'value' => $this->resource['general_vision']['realized_profits']['value'],
                    'formatted' => $this->resource['general_vision']['realized_profits']['formatted'],
                ],
                'expected_profits' => [
                    'value' => $this->resource['general_vision']['expected_profits']['value'],
                    'formatted' => $this->resource['general_vision']['expected_profits']['formatted'],
                ],
                'investment_count' => [
                    'value' => $this->resource['general_vision']['investment_count']['value'],
                    'formatted' => $this->resource['general_vision']['investment_count']['formatted'],
                ],
                'purchase_value' => [
                    'value' => $this->resource['general_vision']['purchase_value']['value'],
                    'formatted' => $this->resource['general_vision']['purchase_value']['formatted'],
                ],
                'distributed_investments' => [
                    'value' => $this->resource['general_vision']['distributed_investments']['value'],
                    'formatted' => $this->resource['general_vision']['distributed_investments']['formatted'],
                ],
                'profit_percentage' => $this->resource['general_vision']['profit_percentage'],
            ],
            'portfolio_performance' => [
                'realized_profit_percentage' => [
                    'value' => $this->resource['portfolio_performance']['realized_profit_percentage']['value'],
                    'formatted' => $this->resource['portfolio_performance']['realized_profit_percentage']['formatted'],
                    'progress' => $this->resource['portfolio_performance']['realized_profit_percentage']['progress'],
                ],
                'net_profits_so_far' => [
                    'value' => $this->resource['portfolio_performance']['net_profits_so_far']['value'],
                    'formatted' => $this->resource['portfolio_performance']['net_profits_so_far']['formatted'],
                ],
                'total_invested' => [
                    'value' => $this->resource['portfolio_performance']['total_invested']['value'],
                    'formatted' => $this->resource['portfolio_performance']['total_invested']['formatted'],
                ],
                'performance_summary' => $this->resource['portfolio_performance']['performance_summary'],
            ],
            'time_period' => $this->resource['time_period'],
            'date_range' => [
                'start' => $this->resource['date_range']['start']->toISOString(),
                'end' => $this->resource['date_range']['end']->toISOString(),
                'label' => $this->resource['date_range']['label'],
            ],
        ];
    }
}
