# نظام إدارة إصدارات التطبيق
# App Version Management System

## نظرة عامة
تم إنشاء نظام كامل لإدارة إصدارات التطبيق يتضمن:
- API للتحقق من التحديثات المتاحة
- Middleware للتحقق من الإصدار في جميع طلبات API
- لوحة تحكم لإدارة الإصدارات من Dashboard

## المكونات المنفذة

### 1. قاعدة البيانات
**Migration:** `database/migrations/2025_11_29_162945_create_app_versions_table.php`

**الحقول:**
- `version`: رقم الإصدار (مثال: 1.0.0)
- `os`: نظام التشغيل (ios/android)
- `is_mandatory`: هل التحديث إجباري
- `release_notes`: ملاحظات الإصدار بالإنجليزية
- `release_notes_ar`: ملاحظات الإصدار بالعربية
- `is_active`: حالة الإصدار (مفعل/معطل)
- `released_at`: تاريخ الإصدار

### 2. Model
**File:** `app/Models/AppVersion.php`

**الوظائف الرئيسية:**
- `compareVersions()`: مقارنة إصدارين
- `getLatestVersion()`: الحصول على أحدث إصدار لنظام تشغيل معين
- `hasMandatoryUpdate()`: التحقق من وجود تحديث إجباري

### 3. Middleware
**File:** `app/Http/Middleware/CheckAppVersion.php`

**الوظيفة:**
- يتحقق من وجود headers: `x-version` و `x-os`
- إذا كان التحديث إجباري، يرجع خطأ 426 (Upgrade Required)
- يستثني route التحقق من التحديث نفسه

**تم إضافته تلقائياً لجميع API routes** في `bootstrap/app.php`

### 4. API Endpoints

#### التحقق من التحديثات
**Route:** `POST /api/app-version/check-update`

**Request Body:**
```json
{
    "version": "1.0.0",
    "os": "ios" // or "android"
}
```

**Response (تحديث متاح):**
```json
{
    "success": true,
    "message": "يوجد تحديث متاح",
    "result": {
        "update_available": true,
        "is_mandatory": true,
        "current_version": "1.0.0",
        "latest_version": "1.1.0",
        "release_notes": "Bug fixes and improvements",
        "release_notes_ar": "إصلاحات وتحسينات",
        "released_at": "2025-11-29T10:00:00Z"
    }
}
```

**Response (لا يوجد تحديث):**
```json
{
    "success": true,
    "message": "أنت تستخدم أحدث إصدار",
    "result": {
        "update_available": false,
        "current_version": "1.1.0",
        "latest_version": "1.1.0"
    }
}
```

### 5. Admin Dashboard

#### Routes
جميع routes موجودة في `routes/admin.php`:

- `GET /admin/app-versions` - قائمة الإصدارات
- `GET /admin/app-versions/create` - نموذج إنشاء إصدار جديد
- `POST /admin/app-versions` - حفظ إصدار جديد
- `GET /admin/app-versions/{id}` - عرض تفاصيل إصدار
- `GET /admin/app-versions/{id}/edit` - نموذج تعديل إصدار
- `PUT /admin/app-versions/{id}` - تحديث إصدار
- `DELETE /admin/app-versions/{id}` - حذف إصدار
- `POST /admin/app-versions/{id}/toggle-status` - تفعيل/تعطيل إصدار

#### Views
- `resources/views/pages/app-version/index.blade.php` - صفحة القائمة
- `resources/views/pages/app-version/form.blade.php` - نموذج الإنشاء/التعديل
- `resources/views/pages/app-version/show.blade.php` - صفحة التفاصيل

## كيفية الاستخدام

### 1. إضافة إصدار جديد من Dashboard
1. اذهب إلى `/admin/app-versions`
2. اضغط على "إضافة إصدار جديد"
3. املأ البيانات:
   - الإصدار (مثال: 1.0.0)
   - نظام التشغيل (iOS أو Android)
   - حدد "تحديث إجباري" إذا كان التحديث إجباري
   - أضف ملاحظات الإصدار
4. احفظ

### 2. استخدام API من التطبيق

#### التحقق من التحديثات
```javascript
// في التطبيق
const response = await fetch('/api/app-version/check-update', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        version: '1.0.0',
        os: 'ios'
    })
});

const data = await response.json();
if (data.result.update_available) {
    if (data.result.is_mandatory) {
        // إجبار المستخدم على التحديث
        showMandatoryUpdateDialog(data.result);
    } else {
        // عرض خيار التحديث
        showOptionalUpdateDialog(data.result);
    }
}
```

#### إرسال Headers في جميع الطلبات
```javascript
// في جميع API calls
fetch('/api/some-endpoint', {
    headers: {
        'x-version': '1.0.0',
        'x-os': 'ios',
        'Authorization': 'Bearer token'
    }
});
```

### 3. سلوك Middleware

**إذا كان التحديث إجباري:**
- جميع API requests (عدا check-update) سترجع خطأ 426
- Response:
```json
{
    "success": false,
    "message": "يجب تحديث التطبيق إلى الإصدار الأحدث",
    "error_code": "MANDATORY_UPDATE_REQUIRED",
    "result": {
        "update_required": true,
        "is_mandatory": true,
        "current_version": "1.0.0",
        "latest_version": "1.1.0",
        "release_notes": "...",
        "release_notes_ar": "..."
    }
}
```

**إذا لم يكن التحديث إجباري:**
- الطلبات تعمل بشكل طبيعي
- يمكن للمستخدم الاستمرار في استخدام التطبيق

**إذا لم يتم إرسال Headers:**
- الطلبات تعمل بشكل طبيعي (للتوافق مع الإصدارات القديمة)

## ملاحظات مهمة

1. **ترتيب الإصدارات:** النظام يستخدم مقارنة semver (1.0.0, 1.0.1, 1.1.0, إلخ)

2. **الإصدارات المعطلة:** الإصدارات المعطلة (`is_active = false`) لا تظهر في نتائج التحقق من التحديثات

3. **أحدث إصدار:** يتم تحديد أحدث إصدار بناءً على:
   - نظام التشغيل
   - حالة التفعيل
   - رقم الإصدار

4. **التحديث الإجباري:** عند تفعيل `is_mandatory` لإصدار معين، جميع المستخدمين الذين يستخدمون إصدار أقدم سيتم إجبارهم على التحديث

## أمثلة على السيناريوهات

### السيناريو 1: تحديث اختياري
1. إضافة إصدار 1.1.0 مع `is_mandatory = false`
2. المستخدمون على 1.0.0 سيحصلون على إشعار بالتحديث
3. يمكنهم الاستمرار في استخدام التطبيق

### السيناريو 2: تحديث إجباري
1. إضافة إصدار 1.2.0 مع `is_mandatory = true`
2. المستخدمون على 1.1.0 أو أقل سيحصلون على خطأ 426
3. يجب عليهم التحديث للاستمرار

### السيناريو 3: إصدارات متعددة
- iOS: 1.0.0, 1.1.0, 1.2.0
- Android: 1.0.0, 1.1.0
- كل نظام تشغيل له إصداراته الخاصة

## الخطوات التالية

1. **تشغيل Migration:**
```bash
php artisan migrate
```

2. **إضافة إصدارات أولية:**
- اذهب إلى `/admin/app-versions`
- أضف الإصدارات الحالية للتطبيق

3. **تحديث التطبيق:**
- أضف إرسال headers `x-version` و `x-os` في جميع API calls
- استخدم endpoint `/api/app-version/check-update` للتحقق من التحديثات

4. **اختبار النظام:**
- اختبر مع إصدارات مختلفة
- اختبر التحديثات الإجبارية والاختيارية
- تأكد من عمل Middleware بشكل صحيح


