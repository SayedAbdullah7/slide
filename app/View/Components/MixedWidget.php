<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MixedWidget extends Component
{
    public $title;
    public $chartHeight;
    public $chartColor;
    public $description;
    public $buttonText;
    public $buttonClass;
    public $buttonAction;
    public $legendItems;
    public $menuItems;

    /**
     * Create a new component instance.
     *
     * @param string $title
     * @param int $chartHeight
     * @param string $chartColor
     * @param string $description
     * @param string $buttonText
     * @param string $buttonClass
     * @param string $buttonAction
     * @param array $legendItems
     * @param array $menuItems
     */
    public function __construct(
        string $title = 'User Base',
        int $chartHeight = 300,
        string $chartColor = 'primary',
        string $description = 'Long before you sit down to put the make sure you breathe',
        string $buttonText = 'Increase Users',
        string $buttonClass = 'btn-danger',
        string $buttonAction = '#',
        array $legendItems = [],
        array $menuItems = []
    ) {
        $this->title = $title;
        $this->chartHeight = $chartHeight;
        $this->chartColor = $chartColor;
        $this->description = $description;
        $this->buttonText = $buttonText;
        $this->buttonClass = $buttonClass;
        $this->buttonAction = $buttonAction;
        $this->legendItems = $legendItems;
        $this->menuItems = $menuItems;

        // Set default legend items if none provided
        if (empty($this->legendItems)) {
            $this->legendItems = [
                [
                    'color' => 'primary',
                    'label' => 'Amount X'
                ],
                [
                    'color' => 'success',
                    'label' => 'Amount Y'
                ]
            ];
        }

        // Set default menu items if none provided
        if (empty($this->menuItems)) {
            $this->menuItems = [
                'heading' => 'Payments',
                'items' => [
                    [
                        'text' => 'Create Invoice',
                        'url' => '#'
                    ],
                    [
                        'text' => 'Create Payment',
                        'url' => '#',
                        'tooltip' => 'Specify a target name for future usage and reference'
                    ],
                    [
                        'text' => 'Generate Bill',
                        'url' => '#'
                    ],
                    [
                        'text' => 'Subscription',
                        'url' => '#',
                        'submenu' => [
                            [
                                'text' => 'Plans',
                                'url' => '#'
                            ],
                            [
                                'text' => 'Billing',
                                'url' => '#'
                            ],
                            [
                                'text' => 'Statements',
                                'url' => '#'
                            ]
                        ],
                        'switch' => [
                            'name' => 'notifications',
                            'label' => 'Recuring',
                            'checked' => true
                        ]
                    ],
                    [
                        'text' => 'Settings',
                        'url' => '#',
                        'separator' => true
                    ]
                ]
            ];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mixed-widget');
    }
}
