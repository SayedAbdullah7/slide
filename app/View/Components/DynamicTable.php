<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DynamicTable extends Component
{
    public $tableId;
    public $columns;
    public $actions;
    public $showCheckbox;
    public $defaultOrder;
    public $ajaxUrl;
    public $JsColumns;
    public $filters;
    public $createUrl;
    /**
     * createUrl a new component instance.
     *
     * @param  string  $tableId
     * @param  array  $columns
     * @param  bool  $actions
     * @param  bool  $showCheckbox
     * @param  array  $defaultOrder
     * @param  string  $ajaxUrl
     * @return void
     */
    public function __construct(
        $tableId = null,
        $columns = [],
        $actions = false,
        $showCheckbox = false,
        $defaultOrder = null,
        $ajaxUrl = null,
        $filters = [],
        $createUrl = null
    )
    {
        $this->createUrl = $createUrl;
        $this->tableId = $tableId;
        $this->columns = $columns;
        $this->actions = $actions;
        $this->showCheckbox = $showCheckbox;
        $this->defaultOrder = $defaultOrder;
        $this->ajaxUrl = $ajaxUrl ?: url()->current();

        $JsColumns = [];
        if ($this->showCheckbox) {
            $JsColumns[] = ['data' => '', 'name' => ''];
        }
        $JsColumns = array_merge($JsColumns, array_map(function($item) {
            if (!is_array($item)){
                $item = $item->toArray();
            }
            return [
                'data' => $item['name'],
                'name' => $item['name'],
                'searchable' => $item['searchable'] ?? true,
                'orderable' => $item['orderable'] ?? true,
            ];
        }, $this->columns));

        if ($this->actions) {
            $JsColumns[] = ['data' => null, 'name' => null];
        }
        $this->JsColumns = $JsColumns;
        $this->filters = $filters;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dynamic-table');
    }


}
