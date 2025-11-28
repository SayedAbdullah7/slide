# Owner Investment Opportunity Request API Documentation

## Overview
هذا API يسمح للمالكين (Owners) بتقديم طلبات فرص استثمارية جديدة. يتضمن جميع البيانات المطلوبة من النموذج بالإضافة إلى اختيار نوع الرهن بشكل اختياري.

## Authentication
جميع endpoints تتطلب authentication باستخدام Laravel Sanctum.

```
Authorization: Bearer {token}
```

## Base URL
```
/api/owner/opportunity-requests
```

## Endpoints

### 1. Get All Requests
**GET** `/api/owner/opportunity-requests`

يحصل على جميع طلبات الفرص الاستثمارية للمالك المصادق عليه.

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "owner_profile_id": 1,
        "company_age": "15 سنة",
        "commercial_experience": "15 سنة",
        "net_profit_margins": "40%",
        "required_amount": "100000.00",
        "description": "إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي",
        "guarantee_type": "real_estate_mortgage",
        "status": "pending",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "owner_profile": {
          "id": 1,
          "user_id": 1,
          "business_name": "شركة الأغذية العضوية",
          "user": {
            "id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com"
          }
        }
      }
    ]
  }
}
```

### 2. Submit New Request
**POST** `/api/owner/opportunity-requests`

يقدم طلب فرصة استثمارية جديد.

**Request Body:**
```json
{
  "company_age": "15 سنة",
  "commercial_experience": "15 سنة",
  "net_profit_margins": "40%",
  "required_amount": 100000,
  "description": "إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي مع التوسع للأسواق الإقليمية",
  "guarantee_type": "real_estate_mortgage"
}
```

**Validation Rules:**
- `company_age`: nullable, string, max:255
- `commercial_experience`: nullable, string, max:255
- `net_profit_margins`: nullable, string, max:255
- `required_amount`: nullable, numeric, min:0
- `description`: nullable, string, max:5000
- `guarantee_type`: nullable, must be one of the valid guarantee types

**Response:**
```json
{
  "success": true,
  "message": "تم تقديم طلب الفرصة الاستثمارية بنجاح",
  "data": {
    "id": 1,
    "owner_profile_id": 1,
    "company_age": "15 سنة",
    "commercial_experience": "15 سنة",
    "net_profit_margins": "40%",
    "required_amount": "100000.00",
    "description": "إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي",
    "guarantee_type": "real_estate_mortgage",
    "status": "pending",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### 3. Get Specific Request
**GET** `/api/owner/opportunity-requests/{id}`

يحصل على تفاصيل طلب فرصة استثمارية محدد.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "owner_profile_id": 1,
    "company_age": "15 سنة",
    "commercial_experience": "15 سنة",
    "net_profit_margins": "40%",
    "required_amount": "100000.00",
    "description": "إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي",
    "guarantee_type": "real_estate_mortgage",
    "status": "pending",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z",
    "owner_profile": {
      "id": 1,
      "user_id": 1,
      "business_name": "شركة الأغذية العضوية",
      "user": {
        "id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com"
      }
    }
  }
}
```

### 4. Update Request
**PUT** `/api/owner/opportunity-requests/{id}`

يحدث طلب فرصة استثمارية موجود (فقط للطلبات في حالة pending).

**Request Body:** (نفس structure مثل POST)

**Response:**
```json
{
  "success": true,
  "message": "تم تحديث طلب الفرصة الاستثمارية بنجاح",
  "data": {
    // Updated request data
  }
}
```

### 5. Delete Request
**DELETE** `/api/owner/opportunity-requests/{id}`

يحذف طلب فرصة استثمارية (فقط للطلبات في حالة pending).

**Response:**
```json
{
  "success": true,
  "message": "تم حذف طلب الفرصة الاستثمارية بنجاح"
}
```

### 6. Get Guarantee Types
**GET** `/api/owner/opportunity-requests/guarantee-types`

يحصل على أنواع الرهن المتاحة.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "real_estate_mortgage",
      "label": "رهن عقاري",
      "color": "green"
    },
    {
      "value": "bank_guarantee",
      "label": "كفالة بنكية",
      "color": "blue"
    },
    {
      "value": "personal_guarantee",
      "label": "كفالة شخصية",
      "color": "yellow"
    },
    {
      "value": "asset_pledge",
      "label": "رهن أصول",
      "color": "purple"
    },
    {
      "value": "insurance_policy",
      "label": "بوليصة تأمين",
      "color": "indigo"
    },
    {
      "value": "government_guarantee",
      "label": "ضمان حكومي",
      "color": "red"
    },
    {
      "value": "collateral",
      "label": "ضمانات أخرى",
      "color": "gray"
    }
  ]
}
```

### 7. Get Request Statuses
**GET** `/api/owner/opportunity-requests/statuses`

يحصل على جميع حالات الطلبات المتاحة.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "value": "pending",
      "label": "في انتظار المراجعة",
      "color": "yellow"
    },
    {
      "value": "approved",
      "label": "تم قبول الطلب",
      "color": "green"
    },
    {
      "value": "rejected",
      "label": "تم رفض الطلب",
      "color": "red"
    },
    {
      "value": "under_review",
      "label": "قيد المراجعة",
      "color": "blue"
    },
    {
      "value": "cancelled",
      "label": "تم إلغاء الطلب",
      "color": "gray"
    }
  ]
}
```

### 8. Get Statistics
**GET** `/api/owner/opportunity-requests/statistics`

يحصل على إحصائيات طلبات الفرص الاستثمارية للمالك.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_requests": 10,
    "pending_requests": 3,
    "approved_requests": 5,
    "rejected_requests": 2,
    "under_review_requests": 1,
    "cancelled_requests": 1
  }
}
```

## Request Statuses

- `pending`: في انتظار المراجعة
- `approved`: تم قبول الطلب
- `rejected`: تم رفض الطلب
- `under_review`: قيد المراجعة
- `cancelled`: تم إلغاء الطلب

### Status Rules:
- **Editable Statuses**: `pending`, `under_review` - يمكن تعديل الطلب
- **Deletable Statuses**: `pending` فقط - يمكن حذف الطلب
- **Final Statuses**: `approved`, `rejected`, `cancelled` - حالات نهائية لا يمكن تغييرها

## Error Responses

### 404 - Not Found
```json
{
  "success": false,
  "message": "لم يتم العثور على ملف المالك"
}
```

### 400 - Bad Request
```json
{
  "success": false,
  "message": "لا يمكن تعديل الطلب في حالته الحالية"
}
```

### 422 - Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "company_age": ["عمر الشركة لا يجب أن يتجاوز 255 حرف"]
  }
}
```

## Usage Examples

### Submit a new request using cURL
```bash
curl -X POST "https://your-domain.com/api/owner/opportunity-requests" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "company_age": "15 سنة",
    "commercial_experience": "15 سنة",
    "net_profit_margins": "40%",
    "required_amount": 100000,
    "description": "إنتاج وتصنيع الأغذية العضوية الطبيعية للسوق المحلي",
    "guarantee_type": "real_estate_mortgage"
  }'
```

### Get all requests using JavaScript
```javascript
const response = await fetch('/api/owner/opportunity-requests', {
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  }
});

const data = await response.json();
console.log(data);
```
