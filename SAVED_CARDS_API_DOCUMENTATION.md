# Saved Cards API - Documentation

## 📋 Overview

نظام حفظ البطاقات للمستخدمين مع منع التكرار وإدارة البطاقات المحفوظة.

---

## ✨ Features

### 1. **منع التكرار (Anti-Duplication)**
- تحقق من `card_token` قبل الحفظ
- تحقق من `masked_pan` كطبقة أمان إضافية
- Unique constraints في قاعدة البيانات
- Auto-update للبطاقات المكررة

### 2. **البطاقة الافتراضية (Default Card)**
- أول بطاقة تُحفظ تصبح افتراضية تلقائياً
- يمكن تغيير البطاقة الافتراضية
- عند حذف البطاقة الافتراضية، تُعيَّن بطاقة أخرى تلقائياً

### 3. **Soft Delete**
- البطاقات لا تُحذف فعلياً
- تُعطَّل بواسطة `is_active = false`
- يمكن إعادة تفعيلها لاحقاً

### 4. **تتبع الاستخدام**
- حفظ `last_used_at` timestamp
- ترتيب البطاقات حسب آخر استخدام

---

## 🔐 APIs

### Base URL: `/api/cards`

| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| GET | `/` | قائمة البطاقات المحفوظة | Required |
| GET | `/default` | البطاقة الافتراضية | Required |
| GET | `/{cardId}` | تفاصيل بطاقة معينة | Required |
| POST | `/{cardId}/set-default` | تعيين بطاقة كافتراضية | Required |
| DELETE | `/{cardId}` | حذف (تعطيل) بطاقة | Required |

---

## 📝 API Details

### 1. Get User's Saved Cards
**الحصول على قائمة البطاقات المحفوظة**

```http
GET /api/cards
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "card_display_name": "MasterCard ending in 0008",
      "masked_pan": "xxxx-xxxx-xxxx-0008",
      "last_four": "0008",
      "card_brand": "MasterCard",
      "is_default": true,
      "last_used_at": "2025-10-12 18:52:36",
      "created_at": "2025-10-12 15:30:00"
    },
    {
      "id": 2,
      "card_display_name": "Visa ending in 1234",
      "masked_pan": "xxxx-xxxx-xxxx-1234",
      "last_four": "1234",
      "card_brand": "Visa",
      "is_default": false,
      "last_used_at": null,
      "created_at": "2025-10-11 12:00:00"
    }
  ]
}
```

**Notes:**
- البطاقات مرتبة حسب: افتراضي أولاً → آخر استخدام → تاريخ الإنشاء
- `card_token` محجوب للأمان

---

### 2. Get Default Card
**الحصول على البطاقة الافتراضية**

```http
GET /api/cards/default
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "card_display_name": "MasterCard ending in 0008",
    "masked_pan": "xxxx-xxxx-xxxx-0008",
    "last_four": "0008",
    "card_brand": "MasterCard",
    "is_default": true,
    "last_used_at": "2025-10-12 18:52:36"
  }
}
```

**Error Response (No default card):**
```json
{
  "success": false,
  "message": "No default card found"
}
```

---

### 3. Get Card by ID
**الحصول على تفاصيل بطاقة معينة**

```http
GET /api/cards/{cardId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "card_display_name": "MasterCard ending in 0008",
    "masked_pan": "xxxx-xxxx-xxxx-0008",
    "last_four": "0008",
    "card_brand": "MasterCard",
    "is_default": true,
    "last_used_at": "2025-10-12 18:52:36",
    "created_at": "2025-10-12 15:30:00"
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Card not found"
}
```

---

### 4. Set Card as Default
**تعيين بطاقة كافتراضية**

```http
POST /api/cards/{cardId}/set-default
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Card set as default successfully",
  "data": {
    "id": 2,
    "card_display_name": "Visa ending in 1234",
    "is_default": true
  }
}
```

**Behavior:**
- البطاقة المحددة تصبح افتراضية
- جميع البطاقات الأخرى تصبح غير افتراضية تلقائياً
- يُسجَّل الحدث في `payment_logs`

---

### 5. Delete (Deactivate) Card
**حذف (تعطيل) بطاقة**

```http
DELETE /api/cards/{cardId}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Card removed successfully"
}
```

**Behavior:**
- البطاقة تُعطَّل (`is_active = false`) ولا تُحذف فعلياً
- إذا كانت البطاقة المحذوفة افتراضية:
  - تُعيَّن بطاقة أخرى كافتراضية تلقائياً
  - الأولوية لآخر بطاقة مُستخدمة
- يُسجَّل الحدث في `payment_logs`

---

## 🔄 How Cards are Saved

### Automatic Saving via Paymob Webhook

عندما يحفظ المستخدم بطاقته في Paymob:

```
1. User enables "Save Card" during checkout
   ↓
2. Paymob tokenizes the card
   ↓
3. Paymob sends webhook to: POST /api/paymob/tokenized-callback
   {
     "type": "TOKEN",
     "obj": {
       "token": "abc123...",
       "masked_pan": "xxxx-xxxx-xxxx-0008",
       "card_subtype": "MasterCard",
       "email": "user@example.com",
       "order_id": "1018352"
     }
   }
   ↓
4. Backend finds user by email or order_id
   ↓
5. Backend calls: UserCard::getOrCreateCard()
   ↓
6. System checks for duplicates:
   - Check by card_token ✓
   - Check by masked_pan ✓
   ↓
7. If duplicate found:
   - Update existing card
   - Return existing card
   ↓
8. If new card:
   - Create new card
   - If first card → set as default
   - Return new card
```

---

## 🛡️ Anti-Duplication System

### Level 1: Database Constraints

```sql
-- Unique constraint on card_token
UNIQUE (card_token)

-- Unique constraint on user_id + card_token
UNIQUE (user_id, card_token)

-- Unique constraint on user_id + masked_pan (extra safety)
UNIQUE (user_id, masked_pan)
```

### Level 2: Application Logic

```php
UserCard::getOrCreateCard([
    'user_id' => 1,
    'card_token' => 'abc123...',
    'masked_pan' => 'xxxx-xxxx-xxxx-0008',
    'card_brand' => 'MasterCard',
]);

// Process:
// 1. Try to find by: user_id + card_token
// 2. If found → update and return
// 3. Try to find by: user_id + masked_pan
// 4. If found → update token and return
// 5. Create new card
```

### Level 3: Static Method

```php
// Check if card exists
if (UserCard::cardExistsForUser($userId, $cardToken, $maskedPan)) {
    // Card already exists
}
```

---

## 📊 Database Schema

```sql
CREATE TABLE user_cards (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    card_token VARCHAR(255) UNIQUE,
    masked_pan VARCHAR(255),
    card_brand VARCHAR(255),
    paymob_token_id INT,
    paymob_order_id VARCHAR(255),
    paymob_merchant_id INT,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (user_id, card_token),
    UNIQUE (user_id, masked_pan),
    INDEX (user_id),
    INDEX (user_id, is_default),
    INDEX (user_id, is_active)
);
```

---

## 🎯 Usage Examples

### Frontend Example (React/JavaScript)

```javascript
// Get user's saved cards
const fetchCards = async () => {
  const response = await fetch('/api/cards', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  const data = await response.json();
  return data.data; // Array of cards
};

// Set card as default
const setDefaultCard = async (cardId) => {
  const response = await fetch(`/api/cards/${cardId}/set-default`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

// Delete card
const deleteCard = async (cardId) => {
  const response = await fetch(`/api/cards/${cardId}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

// Display cards
const CardsList = ({ cards }) => {
  return (
    <div>
      {cards.map(card => (
        <div key={card.id} className="card-item">
          <div className="card-info">
            <span>{card.card_display_name}</span>
            {card.is_default && <span className="badge">Default</span>}
          </div>
          <div className="card-actions">
            {!card.is_default && (
              <button onClick={() => setDefaultCard(card.id)}>
                Set as Default
              </button>
            )}
            <button onClick={() => deleteCard(card.id)}>
              Remove
            </button>
          </div>
        </div>
      ))}
    </div>
  );
};
```

### Mobile Example (Flutter/Dart)

```dart
// Fetch saved cards
Future<List<Card>> fetchSavedCards() async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/cards'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return (data['data'] as List)
        .map((card) => Card.fromJson(card))
        .toList();
  }
  throw Exception('Failed to load cards');
}

// Set card as default
Future<void> setCardAsDefault(int cardId) async {
  final response = await http.post(
    Uri.parse('$baseUrl/api/cards/$cardId/set-default'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
  );
  
  if (response.statusCode != 200) {
    throw Exception('Failed to set default card');
  }
}

// Delete card
Future<void> deleteCard(int cardId) async {
  final response = await http.delete(
    Uri.parse('$baseUrl/api/cards/$cardId'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
  );
  
  if (response.statusCode != 200) {
    throw Exception('Failed to delete card');
  }
}
```

---

## 🔒 Security Features

### 1. **Token Hidden from API**
```php
protected $hidden = [
    'card_token', // Never returned in API responses
];
```

### 2. **User Isolation**
- كل API تتحقق من `user_id`
- المستخدم يرى بطاقاته فقط
- لا يمكن الوصول لبطاقات مستخدمين آخرين

### 3. **No Sensitive Data Logged**
```php
PaymentLog::info('Card saved', [
    'card_id' => $card->id,
    'masked_pan' => $card->masked_pan, // Safe
    // 'card_token' => NEVER LOGGED
]);
```

### 4. **Soft Delete**
- البطاقات المحذوفة تُحفظ في قاعدة البيانات
- يمكن استعادتها أو مراجعتها لاحقاً
- لا تُعرض في الـ APIs العادية

---

## 📝 Model Methods

### Helper Methods

```php
// Get card display name
$card->card_display_name; // "Visa ending in 1234"

// Get last 4 digits
$card->last_four; // "1234"

// Set as default
$card->setAsDefault();

// Mark as used
$card->markAsUsed();

// Check if card exists
UserCard::cardExistsForUser($userId, $cardToken, $maskedPan);

// Get or create (prevent duplicates)
UserCard::getOrCreateCard([...]);
```

### Scopes

```php
// Get active cards only
UserCard::active()->get();

// Get default card
UserCard::default()->first();

// Combine scopes
UserCard::where('user_id', 1)
    ->active()
    ->orderBy('is_default', 'desc')
    ->get();
```

---

## 🧪 Testing

### Test Card Saving

```bash
# Simulate Paymob tokenized callback
curl -X POST http://localhost:8000/api/paymob/tokenized-callback \
  -H "Content-Type: application/json" \
  -d '{
    "type": "TOKEN",
    "obj": {
      "id": 27506,
      "token": "test_token_123",
      "masked_pan": "xxxx-xxxx-xxxx-0008",
      "card_subtype": "MasterCard",
      "email": "user@example.com",
      "order_id": "1018352"
    }
  }'
```

### Test APIs

```bash
# Get saved cards
curl -X GET http://localhost:8000/api/cards \
  -H "Authorization: Bearer {token}"

# Set as default
curl -X POST http://localhost:8000/api/cards/1/set-default \
  -H "Authorization: Bearer {token}"

# Delete card
curl -X DELETE http://localhost:8000/api/cards/1 \
  -H "Authorization: Bearer {token}"
```

---

## ⚠️ Important Notes

### 1. First Card Auto-Default
البطاقة الأولى للمستخدم تصبح افتراضية تلقائياً.

### 2. Duplicate Prevention
- النظام يتعامل مع المحاولات المكررة تلقائياً
- يُحدِّث البطاقة الموجودة بدلاً من رفض الطلب
- يُعيد تفعيل البطاقة المُعطَّلة

### 3. Soft Delete Behavior
- `DELETE /api/cards/{id}` لا يحذف فعلياً
- يُعيِّن `is_active = false`
- البطاقات المُعطَّلة لا تظهر في الـ APIs

### 4. Card Token Security
- `card_token` محجوب من API responses
- يُستخدم فقط في backend للمدفوعات
- لا يُرسَل للـ frontend أبداً

---

## 📚 Related Documentation

- `PAYMOB_WEBHOOKS_DOCUMENTATION.md` - Webhook documentation
- `PAYMOB_WEBHOOK_PAYLOAD_STRUCTURE.md` - Payload structure
- `PAYMENT_APIS_SIMPLIFIED.md` - Payment APIs

---

**Version:** 1.0.0  
**Last Updated:** 2025-10-12





