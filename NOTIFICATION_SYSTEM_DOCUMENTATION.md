# نظام الإشعارات - Notifications System

## نظرة عامة

تم إنشاء نظام إشعارات متكامل باستخدام Laravel Notifications مع Firebase Cloud Messaging (FCM). النظام يدعم:
- ✅ تسجيل الإشعارات في قاعدة البيانات
- ✅ إرسال إشعارات Push عبر Firebase
- ✅ API endpoints كاملة للإشعارات
- ✅ إشعارات تلقائية للأحداث المهمة
- ✅ إشعارات مخصصة من الداشبورد

## البنية الأساسية

### 1. Database

جدول `notifications` يستخدم جدول Laravel الافتراضي:
- `id` (UUID)
- `type` - نوع الإشعار
- `notifiable_type` & `notifiable_id` - المستخدم المستلم
- `data` - بيانات الإشعار (JSON)
- `read_at` - تاريخ القراءة
- `created_at` & `updated_at`

### 2. Notification Classes

#### WalletRechargedNotification
إشعار عند شحن المحفظة
- **الحالة**: عند نجاح شحن المحفظة من بوابة الدفع
- **المكان**: `PaymentWebhookService::executeWalletCharge()`

#### InvestmentPurchasedNotification
إشعار عند شراء فرصة استثمارية
- **الحالة**: عند إنشاء استثمار جديد
- **المكان**: `SendInvestmentPurchasedNotification` Listener
- **Event**: `InvestmentCreated`

#### ProfitDistributedNotification
إشعار عند توزيع الأرباح
- **الحالة**: عند توزيع أرباح الاستثمار
- **المكان**: `DistributionService::distributeReturns()`

#### InvestmentOpportunityAvailableNotification
إشعار عند توفر فرصة استثمارية
- **الحالة**: عند فتح فرصة استثمارية أو إرسال تذكير
- **المكان**: `InvestmentOpportunityAvailable` Event

#### CustomNotification
إشعار مخصص من الداشبورد
- **الحالة**: إرسال إشعارات مخصصة
- **المكان**: `AdminNotificationController`

### 3. Firebase Channel

`FirebaseChannel` - Custom channel للتعامل مع Firebase:
- Location: `app/Notifications/Channels/FirebaseChannel.php`
- Registered in: `AppServiceProvider::boot()`
- Channel name: `firebase`

### 4. Event Listeners

#### SendInvestmentPurchasedNotification
- Listens to: `InvestmentCreated` event
- Location: `app/Listeners/SendInvestmentPurchasedNotification.php`
- Registered in: `EventServiceProvider`

## API Endpoints

### User Notifications (Authenticated)

#### GET `/api/notifications`
جلب الإشعارات الخاصة بالمستخدم

**Query Parameters:**
- `per_page` - عدد الإشعارات في الصفحة (افتراضي: 15)
- `type` - فلترة حسب النوع
- `read` - فلترة حسب القراءة (`read`, `unread`, أو `null` للكل)

**Response:**
```json
{
  "success": true,
  "message": "Notifications retrieved successfully",
  "data": {
    "notifications": [...],
    "unread_count": 5,
    "total_count": 20,
    "pagination": {...}
  }
}
```

#### GET `/api/notifications/unread-count`
جلب عدد الإشعارات غير المقروءة

#### GET `/api/notifications/stats`
جلب إحصائيات الإشعارات

#### POST `/api/notifications/{id}/read`
تحديد إشعار كمقروء

#### POST `/api/notifications/mark-all-read`
تحديد جميع الإشعارات كمقروءة

#### DELETE `/api/notifications/{id}`
حذف إشعار

#### DELETE `/api/notifications`
حذف جميع الإشعارات

### Admin Notifications (Admin Only)

#### POST `/admin/notifications/send-to-users`
إرسال إشعار لمستخدمين محددين

**Request:**
```json
{
  "user_ids": [1, 2, 3],
  "title": "عنوان الإشعار",
  "body": "نص الإشعار",
  "data": {},
  "click_action": "home"
}
```

#### POST `/admin/notifications/send-to-all`
إرسال إشعار لجميع المستخدمين النشطين

**Request:**
```json
{
  "title": "عنوان الإشعار",
  "body": "نص الإشعار",
  "data": {},
  "click_action": "home",
  "profile_type": "investor|owner|all"
}
```

#### POST `/admin/notifications/send-to-investors`
إرسال إشعار للمستثمرين فقط

#### POST `/admin/notifications/send-to-owners`
إرسال إشعار لأصحاب الفرص فقط

## الإشعارات التلقائية

### 1. شحن المحفظة
**متى**: عند نجاح شحن المحفظة من بوابة الدفع
**الكود**: 
```php
// في PaymentWebhookService::executeWalletCharge()
$user->notify(new WalletRechargedNotification(
    $amountSar,
    $wallet->fresh()->balance,
    'payment_gateway'
));
```

### 2. شراء استثمار
**متى**: عند إنشاء استثمار جديد
**الكود**: 
```php
// في SendInvestmentPurchasedNotification Listener
$user->notify(new InvestmentPurchasedNotification($investment));
```

### 3. توزيع الأرباح
**متى**: عند توزيع أرباح الاستثمار
**الكود**: 
```php
// في DistributionService::distributeReturns()
$user->notify(new ProfitDistributedNotification(
    $investment,
    $amount,
    $balance
));
```

### 4. فرصة استثمارية متاحة
**متى**: عند فتح فرصة استثمارية أو إرسال تذكير
**الكود**: 
```php
// في InvestmentOpportunityAvailable Event
$user->notify(new InvestmentOpportunityAvailableNotification(
    $opportunity,
    $reminder
));
```

## الاستخدام

### إرسال إشعار مخصص

#### من الكود:
```php
use App\Notifications\CustomNotification;

$user->notify(new CustomNotification(
    'عنوان الإشعار',
    'نص الإشعار',
    ['custom_data' => 'value'],
    'click_action'
));
```

#### من API (Admin):
```bash
POST /admin/notifications/send-to-users
{
  "user_ids": [1, 2, 3],
  "title": "إشعار مهم",
  "body": "هذا إشعار مخصص",
  "click_action": "investment_opportunity"
}
```

### الحصول على الإشعارات (User):
```bash
GET /api/notifications?per_page=20&read=unread
```

### تحديد إشعار كمقروء:
```bash
POST /api/notifications/{notification_id}/read
```

## Firebase Configuration

الإشعارات يتم إرسالها عبر Firebase Channel الذي يستخدم `FirebaseNotificationService` الموجود.

**ملاحظة**: تأكد من:
1. إعداد Firebase credentials في `.env`
2. وجود ملف `firebase-credentials.json` في `storage/app/`
3. تسجيل FCM tokens للمستخدمين

## Testing

### اختبار إشعار من Terminal:
```php
$user = User::find(1);
$user->notify(new CustomNotification(
    'Test Notification',
    'This is a test notification',
    ['test' => true]
));
```

### اختبار من API:
```bash
# Test notification endpoint
POST /api/fcm/test
Authorization: Bearer {token}
```

## Migration

لتطبيق التغييرات على قاعدة البيانات:
```bash
php artisan migrate
```

## Queue

جميع الإشعارات تستخدم Queue (ShouldQueue interface) لتحسين الأداء. تأكد من:
1. تشغيل Queue Worker:
```bash
php artisan queue:work
```

أو استخدام Supervisor للتشغيل التلقائي.

## Troubleshooting

### الإشعارات لا تُرسل:
1. تحقق من إعدادات Firebase
2. تحقق من FCM tokens النشطة
3. تحقق من Queue worker
4. راجع Logs: `storage/logs/laravel.log`

### الإشعارات لا تُحفظ في Database:
1. تحقق من migration
2. تحقق من `via()` method في Notification class (يجب أن تحتوي على `'database'`)

### Firebase errors:
1. تحقق من Firebase credentials
2. تحقق من صحة FCM tokens
3. راجع `FirebaseNotificationService` logs

## Future Improvements

- [ ] إضافة Email notifications
- [ ] إضافة SMS notifications
- [ ] إضافة Notification preferences للمستخدمين
- [ ] إضافة Scheduled notifications
- [ ] إضافة Notification templates
- [ ] إضافة Analytics للإشعارات

## ملفات مهمة

- `app/Notifications/` - Notification classes
- `app/Notifications/Channels/FirebaseChannel.php` - Firebase channel
- `app/Http/Controllers/Api/NotificationController.php` - User API
- `app/Http/Controllers/Admin/AdminNotificationController.php` - Admin API
- `app/Listeners/SendInvestmentPurchasedNotification.php` - Event listener
- `app/Providers/EventServiceProvider.php` - Event registration
- `app/Providers/AppServiceProvider.php` - Firebase channel registration
- `database/migrations/2025_11_04_203553_create_notifications_table.php` - Migration









