<?php

namespace App\DataTables\Custom;

use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Facades\DataTables;

abstract class BaseDataTable
{
    /**
     * Define searchable relations (relation => columns).
     */
    protected array $searchableRelations = [];

    /**
     * Define columns for DataTable.
     */
    protected array $columns = [];

    /**
     * Define filters for DataTable.
     */
    protected array $filters = [];

    /**
     * Enable caching for search results (disabled by default for real-time data)
     */
    protected bool $enableCache = false;

    /**
     * Cache duration in seconds (5 minutes)
     */
    protected int $cacheDuration = 300;

    /**
     * Returns an array of columns for the DataTable.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return array_map(function ($column) {
            return $this->normalizeColumnDefinition($column);
        }, $this->columns);
    }

    /**
     * Normalize column definition to ensure all required keys exist.
     *
     * @param array $column
     * @return array
     */
    private function normalizeColumnDefinition(array $column): array
    {
        $normalized = array_merge([
            'data' => $column['data'] ?? '',
            'name' => $column['name'] ?? $column['data'] ?? '',
            'title' => $column['title'] ?? ucfirst(str_replace('_', ' ', $column['data'] ?? '')),
            'searchable' => $column['searchable'] ?? true,
            'orderable' => $column['orderable'] ?? true,
            'visible' => $column['visible'] ?? true,
        ], $column);

        // Include className and width if they exist
        if (isset($column['className'])) {
            $normalized['className'] = $column['className'];
        }

        if (isset($column['width'])) {
            $normalized['width'] = $column['width'];
        }

        return $normalized;
    }

    /**
     * Handle global search and related model searches.
     *
     * @param Builder $query
     */
    protected function applySearch(Builder $query): void
    {
        $search = request()->input('search.value');
        if (!$search || strlen(trim($search)) < 2) {
            return;
        }

        // Escape search term to prevent SQL injection
        $searchTerm = '%' . trim($search) . '%';

        // Optimized search: Group all relation searches into a single WHERE clause
        if (count($this->searchableRelations) > 0) {
            $query->orWhere(function ($subQuery) use ($searchTerm) {
                foreach ($this->searchableRelations as $relation => $columns) {
                    $subQuery->orWhereHas($relation, function ($q) use ($columns, $searchTerm) {
                        $q->where(function ($q) use ($columns, $searchTerm) {
                            foreach ($columns as $column) {
                                // Use LOWER for case-insensitive search (works with indexes)
                                $q->orWhereRaw("LOWER({$column}) LIKE ?", [strtolower($searchTerm)]);
                            }
                        });
                    });
                }
            });
        }
    }

    /**
     * Get columns as array
     *
     * @return array
     */
    public function columns(): array
    {
        return $this->getColumns();
    }

    /**
     * Get filters as array
     *
     * @return array
     */
    public function filters(): array
    {
        return $this->filters;
    }


    /**
     * Automatically apply filters to the query based on filter definitions.
     * This method is called automatically in the filter() callback.
     *
     * @param Builder $query
     * @return void
     */
    protected function applyFilters(Builder $query): void
    {
        $filters = $this->filters();

        foreach ($filters as $key => $filterConfig) {
            $filterValue = request()->input($key);

            // Skip if no filter value
            if ($filterValue === null || $filterValue === '') {
                continue;
            }

            // Get column name (use custom column or default to key)
            $column = $filterConfig['column'] ?? $key;
            $type = $filterConfig['type'] ?? 'select';

            // Handle custom query filters
            if ($type === 'select-custom' && isset($filterConfig['callback'])) {
                // Execute custom callback with query and filter value
                $callback = $filterConfig['callback'];
                if (is_callable($callback)) {
                    $callback($query, $filterValue);
                }
                continue;
            }

            switch ($type) {
                case 'select':
                case 'boolean':
                    // Exact match
                    $query->where($column, $filterValue);
                    break;

                case 'text':
                    // LIKE search
                    $query->whereRaw("LOWER({$column}) LIKE ?", ['%' . strtolower($filterValue) . '%']);
                    break;

                case 'date':
                    // Date match
                    $query->whereDate($column, $filterValue);
                    break;

                case 'date-range':
                    // Date range
                    $from = request()->input($key . '_from');
                    $to = request()->input($key . '_to');
                    if ($from) {
                        $query->whereDate($column, '>=', $from);
                    }
                    if ($to) {
                        $query->whereDate($column, '<=', $to);
                    }
                    break;

                case 'number':
                    // Exact number match
                    $query->where($column, $filterValue);
                    break;

                case 'range':
                    // Number range
                    $min = request()->input($key . '_min');
                    $max = request()->input($key . '_max');
                    if ($min !== null && $min !== '') {
                        $query->where($column, '>=', $min);
                    }
                    if ($max !== null && $max !== '') {
                        $query->where($column, '<=', $max);
                    }
                    break;
            }
        }
    }

    /**
     * Abstract method to handle data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    abstract public function handle();
}
