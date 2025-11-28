# Mixed Widget Component Usage

## Basic Usage

```blade
<x-mixed-widget />
```

## Custom Usage Examples

### 1. Simple Custom Widget
```blade
<x-mixed-widget 
    title="Investment Analytics"
    description="Track your investment performance and growth over time"
    button-text="View Analytics"
    button-class="btn-primary"
    button-action="/analytics"
    :chart-height="350"
    chart-color="success"
/>
```

### 2. Custom Legend Items
```blade
<x-mixed-widget 
    title="Revenue Overview"
    :legend-items="[
        ['color' => 'primary', 'label' => 'Total Revenue'],
        ['color' => 'success', 'label' => 'Net Profit'],
        ['color' => 'warning', 'label' => 'Pending']
    ]"
/>
```

### 3. Custom Menu Items
```blade
<x-mixed-widget 
    title="Investment Dashboard"
    :menu-items="[
        'heading' => 'Investment Actions',
        'items' => [
            ['text' => 'Create Investment', 'url' => '/investments/create'],
            ['text' => 'View Reports', 'url' => '/reports'],
            ['text' => 'Export Data', 'url' => '/export'],
            [
                'text' => 'Settings',
                'url' => '#',
                'submenu' => [
                    ['text' => 'Account Settings', 'url' => '/settings/account'],
                    ['text' => 'Notifications', 'url' => '/settings/notifications'],
                    ['text' => 'Privacy', 'url' => '/settings/privacy']
                ]
            ]
        ]
    ]"
/>
```

### 4. Complete Custom Example
```blade
<x-mixed-widget 
    title="Portfolio Performance"
    description="Monitor your investment portfolio with real-time analytics and insights"
    button-text="Optimize Portfolio"
    button-class="btn-success"
    button-action="/portfolio/optimize"
    :chart-height="400"
    chart-color="info"
    :legend-items="[
        ['color' => 'primary', 'label' => 'Stocks'],
        ['color' => 'success', 'label' => 'Bonds'],
        ['color' => 'warning', 'label' => 'Crypto'],
        ['color' => 'danger', 'label' => 'Commodities']
    ]"
    :menu-items="[
        'heading' => 'Portfolio Actions',
        'items' => [
            ['text' => 'Add Investment', 'url' => '/investments/create'],
            ['text' => 'Rebalance', 'url' => '/portfolio/rebalance'],
            ['text' => 'Generate Report', 'url' => '/reports/portfolio'],
            [
                'text' => 'Analysis',
                'url' => '#',
                'submenu' => [
                    ['text' => 'Risk Analysis', 'url' => '/analysis/risk'],
                    ['text' => 'Performance', 'url' => '/analysis/performance'],
                    ['text' => 'Diversification', 'url' => '/analysis/diversification']
                ],
                'switch' => [
                    'name' => 'auto_rebalance',
                    'label' => 'Auto Rebalance',
                    'checked' => false
                ]
            ],
            ['text' => 'Settings', 'url' => '/portfolio/settings', 'separator' => true]
        ]
    ]"
/>
```

## Component Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `title` | string | 'User Base' | Widget title |
| `chartHeight` | int | 300 | Chart height in pixels |
| `chartColor` | string | 'primary' | Chart color theme |
| `description` | string | 'Long before you sit down...' | Description text |
| `buttonText` | string | 'Increase Users' | Button text |
| `buttonClass` | string | 'btn-danger' | Button CSS classes |
| `buttonAction` | string | '#' | Button action URL |
| `legendItems` | array | Default legend | Array of legend items with color and label |
| `menuItems` | array | Default menu | Menu configuration with items and submenus |

## Legend Items Structure
```php
[
    ['color' => 'primary', 'label' => 'Label 1'],
    ['color' => 'success', 'label' => 'Label 2'],
    // ... more items
]
```

## Menu Items Structure
```php
[
    'heading' => 'Menu Title',
    'items' => [
        ['text' => 'Simple Item', 'url' => '/url'],
        ['text' => 'Item with Tooltip', 'url' => '/url', 'tooltip' => 'Tooltip text'],
        [
            'text' => 'Item with Submenu',
            'url' => '#',
            'submenu' => [
                ['text' => 'Sub Item 1', 'url' => '/sub1'],
                ['text' => 'Sub Item 2', 'url' => '/sub2']
            ],
            'switch' => [
                'name' => 'switch_name',
                'label' => 'Switch Label',
                'checked' => true
            ]
        ],
        ['text' => 'Separated Item', 'url' => '/url', 'separator' => true]
    ]
]
```

## Chart Integration

The component includes a chart container with the class `mixed-widget-17-chart`. You can initialize your chart using JavaScript:

```javascript
// Example with Chart.js or your preferred charting library
const chartElement = document.querySelector('.mixed-widget-17-chart');
// Initialize your chart here
```
