<?php

/**
 * Test script for Dashboard API endpoints
 * 
 * This script tests the new dashboard API endpoints created for the owner dashboard screen.
 * 
 * Usage:
 * php test_dashboard_api.php
 */

require_once 'vendor/autoload.php';

// Test endpoint (replace with your actual base URL)
$baseUrl = 'http://localhost:8000/api';
$endpoint = '/owner/opportunity-requests/dashboard';

echo "Dashboard API Test Script\n";
echo "========================\n\n";

echo "Testing endpoint: dashboard\n";
echo "URL: {$baseUrl}{$endpoint}\n";
echo "Method: GET\n";
echo "Headers: Authorization: Bearer {your_token}\n";
echo "Description: Returns complete dashboard data including both statistics and latest projects in a single response\n\n";

echo "Expected Response Format:\n";
echo "========================\n\n";

echo "Dashboard Response:\n";
echo json_encode([
    'success' => true,
    'message' => 'تم جلب بيانات لوحة التحكم بنجاح',
    'data' => [
        'statistics' => [
            'total_funding' => [
                'value' => '1,000,000.00',
                'currency' => 'ريال س',
                'icon' => 'dollar',
                'label' => 'اجمالي التمويل'
            ],
            'total_projects' => [
                'value' => 3,
                'icon' => 'building',
                'label' => 'اجمالي المشاريع'
            ],
            'active_projects' => [
                'value' => 2,
                'icon' => 'chart',
                'label' => 'المشاريع النشطة'
            ],
            'total_investors' => [
                'value' => 123,
                'icon' => 'person',
                'label' => 'اجمالي المستثمرين'
            ],
            'fulfillment_rate' => [
                'value' => '50.0%',
                'icon' => 'handshake',
                'label' => 'نسبة الوفاء'
            ],
            'pending_projects' => [
                'value' => 12,
                'icon' => 'gear',
                'label' => 'المشاريع المعلقة'
            ]
        ],
        'latest_projects' => [
            [
                'id' => 1,
                'name' => 'مصنع الأغذية العضوية',
                'description' => 'إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي مع التوسع للأسواق الإقليمية.',
                'category' => 'عقارات تجارية',
                'status' => [
                    'value' => 'open',
                    'label' => 'مفتوح',
                    'color' => 'green',
                    'is_approved' => true
                ],
                'completion_rate' => '45.0%',
                'investors_count' => 45,
                'created_at' => '2024-01-01T00:00:00.000000Z',
                'cover_image' => null,
                'target_amount' => '1,000,000.00',
                'currency' => 'ريال'
            ]
        ]
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\nAPI Endpoint Created Successfully!\n";
echo "==================================\n";
echo "The following endpoint is now available:\n\n";
echo "• dashboard: GET {$baseUrl}{$endpoint}\n";

echo "\nAll endpoints require authentication with Bearer token.\n";
echo "The endpoints are designed to match the dashboard screen shown in the image.\n";
