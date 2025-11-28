<?php

/**
 * Test script for Contact API
 * This script tests the contact form submission endpoint
 */

// Configuration
$baseUrl = 'http://localhost:8000/api'; // Adjust this to your actual API URL
$endpoint = '/contact';

// Test data
$testData = [
    'subject' => 'استفسار حول التطبيق',
    'message' => 'أريد الاستفسار عن كيفية استخدام التطبيق والاستثمار في المشاريع المتاحة.'
];

// Function to make HTTP request
function makeRequest($url, $data = null, $method = 'GET', $headers = []) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
        'Content-Type: application/json',
        'Accept: application/json'
    ], $headers));

    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }

    return [
        'response' => json_decode($response, true),
        'http_code' => $httpCode,
        'raw_response' => $response
    ];
}

echo "=== Contact API Test ===\n\n";

// Test 1: Submit contact message (without authentication)
echo "Test 1: Submit contact message (Guest)\n";
echo "URL: {$baseUrl}{$endpoint}\n";
echo "Method: POST\n";
echo "Data: " . json_encode($testData, JSON_UNESCAPED_UNICODE) . "\n\n";

$result = makeRequest($baseUrl . $endpoint, $testData, 'POST');

echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Response: " . json_encode($result['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

if ($result['http_code'] === 200 || $result['http_code'] === 201) {
    echo "✅ Test 1 PASSED - Contact message submitted successfully\n\n";

    // Extract message ID for further testing
    $messageId = $result['response']['data']['id'] ?? null;

    if ($messageId) {
        // Test 2: Get contact messages (requires authentication)
        echo "Test 2: Get contact messages (requires authentication)\n";
        echo "URL: {$baseUrl}{$endpoint}\n";
        echo "Method: GET\n";
        echo "Note: This will fail without authentication token\n\n";

        $result2 = makeRequest($baseUrl . $endpoint, null, 'GET');

        echo "HTTP Code: " . $result2['http_code'] . "\n";
        echo "Response: " . json_encode($result2['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

        if ($result2['http_code'] === 401) {
            echo "✅ Test 2 PASSED - Authentication required as expected\n\n";
        } else {
            echo "❌ Test 2 FAILED - Expected 401 Unauthorized\n\n";
        }

        // Test 3: Get specific contact message (requires authentication)
        echo "Test 3: Get specific contact message (requires authentication)\n";
        echo "URL: {$baseUrl}{$endpoint}/{$messageId}\n";
        echo "Method: GET\n";
        echo "Note: This will fail without authentication token\n\n";

        $result3 = makeRequest($baseUrl . $endpoint . '/' . $messageId, null, 'GET');

        echo "HTTP Code: " . $result3['http_code'] . "\n";
        echo "Response: " . json_encode($result3['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

        if ($result3['http_code'] === 401) {
            echo "✅ Test 3 PASSED - Authentication required as expected\n\n";
        } else {
            echo "❌ Test 3 FAILED - Expected 401 Unauthorized\n\n";
        }
    }
} else {
    echo "❌ Test 1 FAILED - Contact message submission failed\n\n";
}

// Test 4: Validation test - empty data
echo "Test 4: Validation test - empty data\n";
echo "URL: {$baseUrl}{$endpoint}\n";
echo "Method: POST\n";
echo "Data: {}\n\n";

$result4 = makeRequest($baseUrl . $endpoint, [], 'POST');

echo "HTTP Code: " . $result4['http_code'] . "\n";
echo "Response: " . json_encode($result4['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

if ($result4['http_code'] === 422) {
    echo "✅ Test 4 PASSED - Validation errors returned as expected\n\n";
} else {
    echo "❌ Test 4 FAILED - Expected 422 Validation Error\n\n";
}

// Test 5: Validation test - long subject
echo "Test 5: Validation test - long subject\n";
echo "URL: {$baseUrl}{$endpoint}\n";
echo "Method: POST\n";

$longSubjectData = [
    'subject' => str_repeat('أ', 300), // 300 characters
    'message' => 'Test message'
];

echo "Data: " . json_encode($longSubjectData, JSON_UNESCAPED_UNICODE) . "\n\n";

$result5 = makeRequest($baseUrl . $endpoint, $longSubjectData, 'POST');

echo "HTTP Code: " . $result5['http_code'] . "\n";
echo "Response: " . json_encode($result5['response'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";

if ($result5['http_code'] === 422) {
    echo "✅ Test 5 PASSED - Subject length validation working\n\n";
} else {
    echo "❌ Test 5 FAILED - Expected 422 Validation Error for long subject\n\n";
}

echo "=== Test Summary ===\n";
echo "Contact API endpoint: {$baseUrl}{$endpoint}\n";
echo "Available methods:\n";
echo "- POST /contact (Submit contact message - Public)\n";
echo "- GET /contact (Get user messages - Requires auth)\n";
echo "- GET /contact/{id} (Get specific message - Requires auth)\n\n";

echo "Profile types supported:\n";
echo "- investor (مستثمر)\n";
echo "- owner (مالك مشروع)\n";
echo "- guest (زائر)\n\n";

echo "Message statuses:\n";
echo "- pending (في الانتظار)\n";
echo "- in_progress (قيد المعالجة)\n";
echo "- resolved (تم الحل)\n";
echo "- closed (مغلق)\n\n";

echo "Test completed!\n";
