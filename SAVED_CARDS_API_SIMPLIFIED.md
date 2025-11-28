# Saved Cards API - Simplified

## 📋 Overview

نظام بسيط لعرض البطاقات المحفوظة للمستخدم مع منع التكرار التلقائي.

---

## 🔐 API

### Get User's Saved Cards
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
- البطاقات مرتبة: الافتراضية أولاً → آخر استخدام → الأحدث
- `card_token` محجوب للأمان (لا يُرسل في الـ response)

---

## 🔄 How Cards Are Saved

### Automatic via Paymob Webhook

البطاقات تُحفظ **تلقائياً** عندما يختار المستخدم "Save Card" في Paymob:

```
1. User enables "Save Card" during checkout
   ↓
2. User completes payment
   ↓
3. Paymob sends webhook to: POST /api/paymob/tokenized-callback
   ↓
4. Backend automatically saves card (with duplicate prevention)
   ↓
5. User can see saved cards via: GET /api/cards
```

---

## 🛡️ Anti-Duplication System

### Automatic Duplicate Prevention

النظام يمنع التكرار **تلقائياً** بدون أي تدخل من المستخدم:

#### Level 1: Database Constraints
```sql
UNIQUE (user_id, card_token)
UNIQUE (user_id, masked_pan)
```

#### Level 2: Application Logic
```php
UserCard::getOrCreateCard([...]);

// If card exists by token → update
// If card exists by masked_pan → update token
// If new card → create
```

### Example Scenarios:

#### Scenario 1: Same Card Token
```
User tries to save: token="abc123", masked_pan="xxxx-0008"
Database has: token="abc123", masked_pan="xxxx-0008"
Result: ✅ Update existing card (no duplicate)
```

#### Scenario 2: Same Card, Different Token
```
User tries to save: token="xyz789", masked_pan="xxxx-0008"
Database has: token="abc123", masked_pan="xxxx-0008"
Result: ✅ Update card with new token (no duplicate)
```

#### Scenario 3: New Card
```
User tries to save: token="new456", masked_pan="xxxx-1234"
Database has: No matching card
Result: ✅ Create new card
```

---

## 🎯 Usage Example

### Frontend (React/Vue/Angular)

```javascript
// Fetch saved cards
const fetchCards = async () => {
  const response = await fetch('/api/cards', {
    headers: {
      'Authorization': `Bearer ${userToken}`,
      'Content-Type': 'application/json'
    }
  });
  
  const result = await response.json();
  
  if (result.success) {
    return result.data; // Array of cards
  }
  
  throw new Error(result.message);
};

// Display cards
const cards = await fetchCards();
console.log(cards);
// [
//   { id: 1, card_display_name: "MasterCard ending in 0008", ... },
//   { id: 2, card_display_name: "Visa ending in 1234", ... }
// ]
```

### Mobile (Flutter)

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

// Card model
class Card {
  final int id;
  final String cardDisplayName;
  final String maskedPan;
  final String lastFour;
  final String cardBrand;
  final bool isDefault;
  final String? lastUsedAt;
  final String createdAt;

  Card.fromJson(Map<String, dynamic> json)
      : id = json['id'],
        cardDisplayName = json['card_display_name'],
        maskedPan = json['masked_pan'],
        lastFour = json['last_four'],
        cardBrand = json['card_brand'],
        isDefault = json['is_default'],
        lastUsedAt = json['last_used_at'],
        createdAt = json['created_at'];
}
```

---

## 🔒 Security

### 1. **Token Hidden**
- `card_token` لا يُرسل أبداً في الـ API responses
- محفوظ فقط في قاعدة البيانات للاستخدام الداخلي

### 2. **User Isolation**
```php
// Each user sees only their cards
UserCard::where('user_id', Auth::id())->get();
```

### 3. **No Sensitive Data**
```php
// Never logged or exposed:
- Full card number
- CVV
- PIN
- card_token (internal use only)

// Safe to display:
- masked_pan (xxxx-xxxx-xxxx-1234)
- card_brand (Visa, MasterCard)
- last_four (1234)
```

---

## 📊 Database Schema

```sql
CREATE TABLE user_cards (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    card_token VARCHAR(255) UNIQUE,
    masked_pan VARCHAR(255),
    card_brand VARCHAR(255),
    paymob_token_id INT,
    paymob_order_id VARCHAR(255),
    paymob_merchant_id INT,
    is_default BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_token (user_id, card_token),
    UNIQUE KEY unique_user_pan (user_id, masked_pan)
);
```

---

## 🎯 Features

### ✅ Automatic Card Saving
- البطاقات تُحفظ تلقائياً عبر Paymob webhook
- لا حاجة لـ manual API call

### ✅ Duplicate Prevention
- التحقق من `card_token`
- التحقق من `masked_pan`
- Database unique constraints
- Auto-update للبطاقات المكررة

### ✅ Default Card
- أول بطاقة تصبح افتراضية تلقائياً
- يظهر `is_default: true` في الـ response

### ✅ Smart Sorting
- البطاقة الافتراضية أولاً
- ثم حسب آخر استخدام
- ثم حسب تاريخ الإنشاء

### ✅ Soft Delete Support
- البطاقات المُعطَّلة لا تظهر
- Scope `active()` يُطبَّق تلقائياً

---

## 🧪 Testing

```bash
# Test the API
curl -X GET http://localhost:8000/api/cards \
  -H "Authorization: Bearer your_token_here"

# Expected response
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
    }
  ]
}
```

---

## 📝 Summary

### One Simple API:
```
GET /api/cards
```

### Features:
✅ View all saved cards  
✅ Automatic duplicate prevention  
✅ Secure (token hidden)  
✅ Sorted (default first)  
✅ Active cards only  

### No Extra APIs Needed:
❌ Set default - not needed (auto-set on first card)  
❌ Delete card - not needed (users don't manage cards)  
❌ Update card - not needed (auto-updated via webhook)  

---

**Version:** 2.0.0 - Simplified  
**Last Updated:** 2025-10-12





