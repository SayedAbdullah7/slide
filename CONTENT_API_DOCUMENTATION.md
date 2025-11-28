# Content API Documentation

This document provides comprehensive information about the Content APIs for the Slide App, which handle static content including Privacy Policy, Terms and Conditions, About App, and FAQ sections.

## Base URL
```
http://your-domain.com/api/content
```

## Authentication
All content endpoints are **public** and do not require authentication.

## API Endpoints

### 1. Get Privacy Policy
Retrieves the privacy policy content in Arabic.

**Endpoint:** `GET /api/content/privacy-policy`

**Response:**
```json
{
    "success": true,
    "message": "Privacy policy retrieved successfully",
    "result": {
        "title": "سياسة الخصوصية",
        "content": "نحن في تطبيق سلايد نلتزم بحماية خصوصيتك...",
        "last_updated": "2025-09-22"
    }
}
```

### 2. Get Terms and Conditions
Retrieves the terms and conditions content in Arabic.

**Endpoint:** `GET /api/content/terms-and-conditions`

**Response:**
```json
{
    "success": true,
    "message": "Terms and conditions retrieved successfully",
    "result": {
        "title": "الشروط والأحكام",
        "content": "باستخدام هذا التطبيق، فإنك توافق على الالتزام...",
        "last_updated": "2025-09-22"
    }
}
```

### 3. Get About App Content
Retrieves the about app content in Arabic.

**Endpoint:** `GET /api/content/about-app`

**Response:**
```json
{
    "success": true,
    "message": "About app content retrieved successfully",
    "result": {
        "title": "عن التطبيق",
        "content": "تطبيق سلايد هو منصة استثمارية مبتكرة...",
        "last_updated": "2025-09-22"
    }
}
```

### 4. Get FAQ List
Retrieves all active FAQs ordered by their display order.

**Endpoint:** `GET /api/content/faq`

**Response:**
```json
{
    "success": true,
    "message": "FAQ list retrieved successfully",
    "result": {
        "faqs": [
            {
                "id": 1,
                "question": "عن تطبيق سلايد؟",
                "answer": "تطبيق سلايد هو منصة استثمارية مبتكرة...",
                "order": 1
            },
            {
                "id": 2,
                "question": "هل طريقة الاستثمار امنة؟",
                "answer": "نعم، نستخدم أحدث تقنيات التشفير...",
                "order": 2
            }
        ],
        "total": 2
    }
}
```

### 5. Get Specific FAQ
Retrieves a specific FAQ by its ID.

**Endpoint:** `GET /api/content/faq/{id}`

**Parameters:**
- `id` (integer, required): The FAQ ID

**Response:**
```json
{
    "success": true,
    "message": "FAQ details retrieved successfully",
    "result": {
        "id": 1,
        "question_ar": "عن تطبيق سلايد؟",
        "question_en": "About Slide App?",
        "answer_ar": "تطبيق سلايد هو منصة استثمارية مبتكرة...",
        "answer_en": "Slide App is an innovative investment platform...",
        "order": 1
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "FAQ not found"
}
```

### 6. Get All Content
Retrieves all static content in a single API call for better performance.

**Endpoint:** `GET /api/content/all`

**Response:**
```json
{
    "success": true,
    "message": "All content retrieved successfully",
    "result": {
        "privacy_policy": {
            "title_ar": "سياسة الخصوصية",
            "title_en": "Privacy Policy",
            "content_ar": "نحن في تطبيق سلايد نلتزم بحماية خصوصيتك...",
            "content_en": "At Slide App, we are committed to protecting your privacy...",
            "last_updated": "2024-01-01"
        },
        "terms_and_conditions": {
            "title_ar": "الشروط والأحكام",
            "title_en": "Terms and Conditions",
            "content_ar": "باستخدام هذا التطبيق، فإنك توافق على الالتزام...",
            "content_en": "By using this application, you agree to abide by...",
            "last_updated": "2024-01-01"
        },
        "about_app": {
            "title_ar": "عن التطبيق",
            "title_en": "About the App",
            "content_ar": "تطبيق سلايد هو منصة استثمارية مبتكرة...",
            "content_en": "Slide App is an innovative investment platform...",
            "last_updated": "2024-01-01"
        },
        "faqs": [
            {
                "id": 1,
                "question_ar": "عن تطبيق سلايد؟",
                "question_en": "About Slide App?",
                "answer_ar": "تطبيق سلايد هو منصة استثمارية مبتكرة...",
                "answer_en": "Slide App is an innovative investment platform...",
                "order": 1
            }
        ],
        "faq_count": 1
    }
}
```

## Error Responses

### 404 Not Found
```json
{
    "success": false,
    "message": "FAQ not found"
}
```

### 500 Server Error
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## Data Models

### FAQ Model
```php
{
    "id": integer,
    "question_ar": string,
    "question_en": string,
    "answer_ar": text,
    "answer_en": text,
    "order": integer,
    "is_active": boolean,
    "created_at": timestamp,
    "updated_at": timestamp
}
```

### Content Response Model
```php
{
    "title_ar": string,
    "title_en": string,
    "content_ar": text,
    "content_en": text,
    "last_updated": date
}
```

## Usage Examples

### JavaScript/Fetch
```javascript
// Get all content
fetch('/api/content/all')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Privacy Policy:', data.result.privacy_policy);
            console.log('FAQs:', data.result.faqs);
        }
    });

// Get FAQ list
fetch('/api/content/faq')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.result.faqs.forEach(faq => {
                console.log(`Q: ${faq.question_ar}`);
                console.log(`A: ${faq.answer_ar}`);
            });
        }
    });
```

### cURL Examples
```bash
# Get privacy policy
curl -X GET "http://localhost:8000/api/content/privacy-policy" \
     -H "Accept: application/json"

# Get FAQ list
curl -X GET "http://localhost:8000/api/content/faq" \
     -H "Accept: application/json"

# Get specific FAQ
curl -X GET "http://localhost:8000/api/content/faq/1" \
     -H "Accept: application/json"

# Get all content
curl -X GET "http://localhost:8000/api/content/all" \
     -H "Accept: application/json"
```

## Database Schema

### FAQs Table
```sql
CREATE TABLE f_a_q_s (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_ar VARCHAR(255) NOT NULL COMMENT 'Question in Arabic',
    question_en VARCHAR(255) NULL COMMENT 'Question in English',
    answer_ar TEXT NOT NULL COMMENT 'Answer in Arabic',
    answer_en TEXT NULL COMMENT 'Answer in English',
    `order` INT DEFAULT 0 COMMENT 'Display order',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether FAQ is active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Notes

1. **Performance**: Use the `/all` endpoint if you need multiple content types to reduce API calls.
2. **Caching**: Consider implementing caching for better performance since content doesn't change frequently.
3. **Localization**: All endpoints support both Arabic and English content.
4. **Ordering**: FAQs are ordered by the `order` field in ascending order.
5. **Active Status**: Only active FAQs are returned in the API responses.
6. **No Authentication**: All endpoints are public and don't require authentication.

## Support

For any issues or questions regarding these APIs, please contact the development team.
