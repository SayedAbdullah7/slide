# تقرير مراجعة كود شراء الفرصة عن طريق بوابة الدفع

## 📋 نظرة عامة

تم مراجعة كود عملية شراء الفرصة الاستثمارية عن طريق بوابة الدفع (Paymob). هذه العملية تمر بعدة مراحل بدءاً من طلب المستثمر وانتهاءً بتنفيذ الاستثمار بعد نجاح الدفع.

---

## 🔄 تدفق العملية

### المرحلة 1: بدء طلب الاستثمار
**الملف:** `app/Http/Controllers/Api/InvestmentOpportunityController.php`
**الدالة:** `invest()`

```
المستخدم → POST /api/investment-opportunities/invest
  ↓
التحقق من البيانات (validation)
  ↓
التحقق من بروفايل المستثمر
  ↓
جلب الفرصة الاستثمارية
  ↓
تحديد طريقة الدفع (card/apple_pay/wallet)
```

**الكود:**
```php
public function invest(Request $request)
{
    // التحقق من البيانات
    $data = $request->validate([
        'investment_opportunity_id' => 'required',
        'shares' => 'required|integer|min:1',
        'type' => 'required|string|in:myself,authorize',
        'pay_by' => 'nullable|string|in:card,apple_pay,wallet,online',
    ]);
    
    $pay_by = $request->input('pay_by', 'wallet');
    if($pay_by == 'online'){
        $pay_by = 'card';
    }
    
    // التحقق من المستثمر والفرصة
    $investor = Auth::user()?->investorProfile;
    $opportunity = InvestmentOpportunity::findOrFail($data['investment_opportunity_id']);
    
    // توجيه حسب طريقة الدفع
    if ($pay_by === 'card' || $pay_by === 'apple_pay') {
        return $this->handleOnlinePayment($investor, $opportunity, $data, $pay_by);
    }
    
    return $this->handleWalletPayment($investor, $opportunity, $data);
}
```

---

### المرحلة 2: إنشاء نية الدفع (Payment Intention)
**الملف:** `app/Http/Controllers/Api/InvestmentOpportunityController.php`
**الدالة:** `handleOnlinePayment()`

```
التحقق من صحة الاستثمار (بدون معالجة الدفع)
  ↓
إنشاء نية الدفع عبر PaymentService
  ↓
إرجاع client_secret للمستخدم
```

**الكود:**
```php
private function handleOnlinePayment($investor, $opportunity, $data, $pay_by)
{
    // التحقق من الاستثمار (validation فقط)
    $this->investmentService->validateInvestment(
        $investor, 
        $opportunity, 
        $data['shares'], 
        $data['type']
    );
    
    // إنشاء نية الدفع
    $result = $this->paymentService->createInvestmentIntention([
        'opportunity_id' => $data['investment_opportunity_id'],
        'shares' => $data['shares'],
        'investment_type' => $data['type'],
        'pay_by' => $pay_by,
    ], Auth::id(), $pay_by);
    
    if ($result['success']) {
        return $this->respondCreated([
            'success' => true,
            'message' => 'تم إنشاء نية الدفع بنجاح',
            'result' => $result['data'], // يحتوي على client_secret
            'payment_required' => true,
        ]);
    }
}
```

---

### المرحلة 3: معالجة نية الدفع
**الملف:** `app/Services/PaymentService.php`
**الدالة:** `processInvestmentIntention()`

```
حساب المبلغ المطلوب (بما في ذلك الرسوم)
  ↓
إعداد بيانات Paymob
  ↓
إنشاء نية الدفع في Paymob
  ↓
حفظ نية الدفع في قاعدة البيانات
```

**الكود:**
```php
private function processInvestmentIntention(array $data, InvestmentOpportunity $opportunity, string $payBy): array
{
    // حساب المبلغ
    $amountSar = $this->calculatorService->calculateInvestmentAmount(
        $data['shares'], 
        $opportunity->share_price
    );
    $totalPaymentRequired = $this->calculatorService->calculateTotalPaymentRequired(
        $amountSar, 
        $data['shares'], 
        $data['investment_type'], 
        $opportunity
    );
    $amountCents = (int) ($totalPaymentRequired * 100);
    
    // إعداد بيانات Paymob
    $paymobData = [
        'user_id' => $data['user_id'],
        'amount_cents' => $amountCents,
        'currency' => 'SAR',
        'type' => 'investment',
        'pay_by' => $payBy,
        'billing_data' => $this->prepareBillingData($user),
        'items' => [[
            'name' => $opportunity->name,
            'amount' => $amountCents,
            'description' => "Investment in {$opportunity->name} ID {$opportunity->id} - {$data['shares']} shares",
            'quantity' => 1
        ]],
        'special_reference' => "INV-{$data['opportunity_id']}-{$data['user_id']}-" . time(),
        'extras' => [
            'opportunity_id' => $data['opportunity_id'],
            'shares' => $data['shares'],
            'investment_type' => $data['investment_type'],
            'share_price' => $opportunity->share_price,
            'opportunity_name' => $opportunity->name,
            'user_id' => $data['user_id'],
        ],
        'card_tokens' => $this->getUserCardTokens($data['user_id']),
    ];
    
    // إنشاء نية الدفع في Paymob
    $result = $this->paymobService->createIntention($paymobData);
    
    // إرجاع client_secret فقط
    if ($result['success'] && isset($result['data'])) {
        $result['data'] = [
            'client_secret' => $result['data']['client_secret'] ?? null,
            'public_key' => config('services.paymob.public_key'),
        ];
    }
    
    return $result;
}
```

---

### المرحلة 4: معالجة استجابة بوابة الدفع (Webhook)
**الملف:** `app/Services/PaymentWebhookService.php`
**الدالة:** `handleWebhook()`

```
استقبال webhook من Paymob
  ↓
التحقق من صحة HMAC signature
  ↓
البحث عن نية الدفع
  ↓
تحديث حالة نية الدفع
  ↓
تنفيذ الاستثمار (إذا نجح الدفع ولم يتم التنفيذ من قبل)
```

**الكود:**
```php
public function handleWebhook(array $data): array
{
    // التحقق من صحة webhook
    $webhook = new PaymobWebhookData($data);
    $verification = $webhook->verify($hmacSecret);
    
    if (!$verification['valid']) {
        return ['success' => false, 'message' => 'Webhook verification failed'];
    }
    
    // البحث عن نية الدفع
    $intention = $webhook->getPaymentIntention();
    
    if ($intention) {
        $this->updateIntentionWithTransaction($intention, $webhook);
    }
    
    return ['success' => true, 'message' => 'Webhook processed successfully'];
}

private function updateIntentionWithTransaction($intention, PaymobWebhookData $webhook): void
{
    // تحديث حالة نية الدفع
    $this->paymentRepository->updateIntention($intention, [
        'status' => $webhook->getIntentionStatus(),
        'transaction_id' => $webhook->getTransactionId(),
        'merchant_order_id' => $webhook->getMerchantOrderId(),
        'payment_method' => $webhook->getPaymentMethod(),
        'paymob_response' => $webhook->getRawData(),
        'processed_at' => now(),
    ]);
    
    // تنفيذ الاستثمار فقط إذا نجح الدفع ولم يتم التنفيذ من قبل
    if ($webhook->isSuccessful() && !$intention->is_executed) {
        $this->executeTransaction($intention);
    }
}
```

---

### المرحلة 5: تنفيذ الاستثمار
**الملف:** `app/Services/PaymentWebhookService.php`
**الدالة:** `executeInvestment()`

```
استخراج بيانات الاستثمار من extras
  ↓
التحقق من وجود الفرصة والمستثمر
  ↓
إنشاء الاستثمار (تخطي خصم المحفظة)
  ↓
تحديث is_executed = true
```

**الكود:**
```php
private function executeInvestment($intention): void
{
    $extras = $intention->extras ?? [];
    $opportunityId = $extras['opportunity_id'] ?? null;
    $shares = $extras['shares'] ?? null;
    
    // التحقق من البيانات
    if (!$opportunityId || !$shares) {
        PaymentLog::error('Missing investment data', [...]);
        return;
    }
    
    $opportunity = InvestmentOpportunity::find($opportunityId);
    if (!$opportunity) {
        PaymentLog::error('Investment opportunity not found', [...]);
        return;
    }
    
    // الحصول على المستثمر
    $investor = \App\Models\InvestorProfile::where('user_id', $intention->user_id)->first();
    if (!$investor) {
        PaymentLog::error('Investor profile not found', [...]);
        return;
    }
    
    // إنشاء الاستثمار (skip wallet payment لأن الدفع تم عبر Paymob)
    $investmentService = app(InvestmentService::class);
    $investment = $investmentService->invest(
        investor: $investor,
        opportunity: $opportunity,
        shares: $shares,
        investmentType: $extras['investment_type'] ?? 'myself',
        skipWalletPayment: true // ✅ مهم: لا نخصم من المحفظة
    );
    
    // تحديد أن الاستثمار تم تنفيذه
    $this->paymentRepository->updateIntention($intention, [
        'is_executed' => true
    ]);
}
```

---

## ✅ النقاط الإيجابية

### 1. **فصل الاهتمامات (Separation of Concerns)**
- الكود منظم بشكل جيد مع فصل واضح بين:
  - Controller (InvestmentOpportunityController)
  - Service (PaymentService, InvestmentService)
  - Webhook Handler (PaymentWebhookService)

### 2. **منع التنفيذ المكرر (Duplicate Prevention)**
- استخدام `is_executed` flag لمنع تنفيذ الاستثمار مرتين
- التحقق من `!$intention->is_executed` قبل التنفيذ

### 3. **التحقق من الأمان**
- التحقق من HMAC signature للـ webhook
- التحقق من صحة البيانات في كل مرحلة

### 4. **التسجيل (Logging)**
- تسجيل شامل للأحداث عبر `PaymentLog`
- تسجيل الأخطاء والنجاحات

### 5. **معالجة الأخطاء**
- استخدام try-catch في الأماكن المناسبة
- إرجاع رسائل خطأ واضحة بالعربية

---

## ⚠️ المشاكل والنقاط التي تحتاج تحسين

### 1. **مشكلة التحقق من وجود الفرصة في Validation**

**الموقع:** `InvestmentOpportunityController.php:103`

```php
'investment_opportunity_id' => 'required',
```

**المشكلة:**
- لا يوجد تحقق من وجود الفرصة في قاعدة البيانات
- يمكن للمستخدم إرسال معرف غير موجود
- يتم التحقق لاحقاً في `findOrFail()` مما قد يسبب خطأ 404 غير واضح

**الحل المقترح:**
```php
'investment_opportunity_id' => 'required|exists:investment_opportunities,id',
```

**ملاحظة:** يوجد تعليق يوضح أن التحقق معطل عن قصد، لكن يجب توثيق السبب.

---

### 2. **مشكلة التحقق من حالة الفرصة في وقت التنفيذ**

**الموقع:** `PaymentWebhookService.php:211-234`

**المشكلة:**
- يتم التحقق من صحة الاستثمار عند إنشاء نية الدفع
- لكن قد تتغير حالة الفرصة بين إنشاء نية الدفع وتنفيذ الاستثمار
- مثال: قد تُغلق الفرصة أو تُكمل التمويل قبل تنفيذ الاستثمار

**الحل المقترح:**
إضافة تحقق إضافي في `executeInvestment()`:

```php
private function executeInvestment($intention): void
{
    // ... الكود الحالي ...
    
    // ⚠️ إضافة: التحقق من حالة الفرصة مرة أخرى
    try {
        $validationService = app(InvestmentValidationService::class);
        $validationService->validateInvestmentOpportunity($opportunity);
    } catch (InvestmentException $e) {
        PaymentLog::error('Opportunity validation failed during execution', [
            'opportunity_id' => $opportunityId,
            'status' => $opportunity->status,
            'error' => $e->getMessage()
        ], $intention->user_id, $intention->id, null, 'opportunity_validation_failed');
        
        // هنا يجب إما:
        // 1. إرجاع المال للمستخدم (refund)
        // 2. أو السماح بالاستثمار مع تسجيل تحذير
        throw $e;
    }
    
    // ... باقي الكود ...
}
```

---

### 3. **مشكلة معالجة الأخطاء في executeInvestment**

**الموقع:** `PaymentWebhookService.php:264-271`

**المشكلة:**
- في حالة فشل تنفيذ الاستثمار، يتم `throw $e` لكن:
  - المال تم خصمه من المستخدم بالفعل
  - الاستثمار لم يتم إنشاؤه
  - لا يوجد آلية لإرجاع المال (refund)

**الحل المقترح:**
```php
catch (Exception $e) {
    PaymentLog::error('Investment failed', [...]);
    
    // ⚠️ مهم: يجب إضافة آلية refund هنا
    // لأن المال تم خصمه بالفعل من المستخدم
    try {
        $this->initiateRefund($intention, $e);
    } catch (Exception $refundException) {
        PaymentLog::error('Refund failed after investment failure', [
            'payment_id' => $intention->id,
            'refund_error' => PaymentLog::formatException($refundException)
        ], $intention->user_id, $intention->id, null, 'refund_failed');
    }
    
    throw $e;
}
```

---

### 4. **استخدام find() بدلاً من findOrFail()**

**الموقع:** `PaymentWebhookService.php:226`

```php
$opportunity = InvestmentOpportunity::find($opportunityId);
```

**المشكلة:**
- استخدام `find()` يعني أن الكود يجب أن يتعامل مع حالة `null`
- لكن في حالة عدم وجود الفرصة، يتم فقط تسجيل الخطأ وإرجاع `return`
- المال ما زال محسوباً من المستخدم

**الحل المقترح:**
```php
try {
    $opportunity = InvestmentOpportunity::findOrFail($opportunityId);
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    PaymentLog::error('Investment opportunity not found', [...]);
    
    // يجب إرجاع المال للمستخدم
    $this->initiateRefund($intention, new Exception('Opportunity not found'));
    
    return;
}
```

---

### 5. **عدم التحقق من تكرار نية الدفع**

**الموقع:** `PaymentService.php:127-181`

**المشكلة:**
- يمكن للمستخدم إنشاء عدة نوايا دفع لنفس الاستثمار
- لا يوجد تحقق لمنع النوايا المكررة

**الحل المقترح:**
إضافة تحقق قبل إنشاء نية الدفع:

```php
private function processInvestmentIntention(array $data, InvestmentOpportunity $opportunity, string $payBy): array
{
    // ⚠️ إضافة: التحقق من وجود نية دفع نشطة لنفس الاستثمار
    $existingIntention = PaymentIntention::where('user_id', $data['user_id'])
        ->where('type', 'investment')
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'failed')
        ->whereJsonContains('extras->opportunity_id', $data['opportunity_id'])
        ->whereJsonContains('extras->shares', $data['shares'])
        ->whereJsonContains('extras->investment_type', $data['investment_type'])
        ->first();
    
    if ($existingIntention) {
        PaymentLog::warning('Duplicate intention attempt', [
            'existing_intention_id' => $existingIntention->id,
            'opportunity_id' => $data['opportunity_id']
        ], $data['user_id'], null, null, 'duplicate_intention');
        
        // إما إرجاع النية الموجودة أو رفض الطلب
        throw new \Exception('يوجد طلب دفع قائم بالفعل لهذا الاستثمار');
    }
    
    // ... باقي الكود ...
}
```

---

### 6. **مشكلة في التحقق من المستثمر**

**الموقع:** `PaymentWebhookService.php:240`

```php
$investor = \App\Models\InvestorProfile::where('user_id', $intention->user_id)->first();
```

**المشكلة:**
- استخدام `first()` يعني أن الكود يجب أن يتعامل مع حالة `null`
- في حالة عدم وجود المستثمر، المال محسوب بالفعل

**الحل المقترح:**
```php
$investor = \App\Models\InvestorProfile::where('user_id', $intention->user_id)->first();

if (!$investor) {
    PaymentLog::error('Investor profile not found', [...]);
    
    // ⚠️ يجب إرجاع المال
    $this->initiateRefund($intention, new Exception('Investor profile not found'));
    
    return;
}
```

---

### 7. **عدم التحقق من تغير سعر السهم**

**الموقع:** `PaymentService.php:153`

**المشكلة:**
- يتم حفظ `share_price` في `extras` عند إنشاء نية الدفع
- لكن قد يتغير سعر السهم قبل تنفيذ الاستثمار
- يتم استخدام السعر القديم في التنفيذ

**الحل المقترح:**
في `executeInvestment()`:

```php
// التحقق من أن سعر السهم لم يتغير
$savedSharePrice = $extras['share_price'] ?? null;
$currentSharePrice = $opportunity->share_price;

if ($savedSharePrice && $savedSharePrice != $currentSharePrice) {
    PaymentLog::warning('Share price changed', [
        'saved_price' => $savedSharePrice,
        'current_price' => $currentSharePrice,
        'opportunity_id' => $opportunityId
    ], $intention->user_id, $intention->id, null, 'share_price_changed');
    
    // إما:
    // 1. رفض الاستثمار وإرجاع المال
    // 2. أو استخدام السعر الحالي مع إشعار المستخدم
    throw new Exception('سعر السهم قد تغير، يرجى المحاولة مرة أخرى');
}
```

---

### 8. **مشكلة Race Condition في حجز الأسهم**

**الموقع:** `InvestmentService.php:212`

```php
$opportunity->reserveShares($shares);
```

**المشكلة:**
- إذا قام مستخدمان بشراء نفس الكمية من الأسهم في نفس الوقت
- قد يحدث race condition في حجز الأسهم

**الحل المقترح:**
استخدام database locks:

```php
return DB::transaction(function () use (...) {
    // Lock the opportunity row
    $opportunity = InvestmentOpportunity::lockForUpdate()
        ->findOrFail($opportunity->id);
    
    // التحقق من توفر الأسهم مرة أخرى
    if ($opportunity->available_shares < $shares) {
        throw InvestmentException::insufficientShares();
    }
    
    // ... باقي الكود ...
});
```

---

### 9. **عدم التحقق من المبلغ المستلم مقابل المبلغ المحفوظ**

**الموقع:** `PaymentWebhookService.php:211`

**المشكلة:**
- المبلغ المحفوظ في `intention.amount_cents` قد يختلف عن المبلغ المستلم من webhook
- لا يوجد تحقق لمطابقة المبالغ

**الحل المقترح:**
```php
private function executeInvestment($intention): void
{
    // ... الكود الحالي ...
    
    // ⚠️ التحقق من المبلغ
    $webhookAmount = $webhook->getAmountCents();
    $intentionAmount = $intention->amount_cents;
    
    if ($webhookAmount != $intentionAmount) {
        PaymentLog::error('Amount mismatch', [
            'intention_amount' => $intentionAmount,
            'webhook_amount' => $webhookAmount,
            'payment_id' => $intention->id
        ], $intention->user_id, $intention->id, null, 'amount_mismatch');
        
        // يجب رفض التنفيذ وإرجاع المال
        throw new Exception('Amount mismatch detected');
    }
    
    // ... باقي الكود ...
}
```

---

### 10. **عدم وجود آلية لإعادة المحاولة (Retry Mechanism)**

**المشكلة:**
- في حالة فشل تنفيذ الاستثمار بسبب خطأ مؤقت (مثل مشكلة في قاعدة البيانات)
- لا توجد آلية لإعادة المحاولة

**الحل المقترح:**
استخدام Laravel Queue مع retry:

```php
// في executeInvestment()
try {
    $investment = $investmentService->invest(...);
} catch (Exception $e) {
    // إذا كان الخطأ مؤقت، أعد المحاولة
    if ($this->isRetryableError($e)) {
        // إعادة المحاولة بعد 5 ثوان
        dispatch(new ExecuteInvestmentJob($intention))
            ->delay(now()->addSeconds(5));
        
        return;
    }
    
    // خطأ دائم، إرجاع المال
    $this->initiateRefund($intention, $e);
    throw $e;
}
```

---

## 🔒 نقاط الأمان

### 1. ✅ التحقق من HMAC Signature
- يتم التحقق من صحة webhook عبر HMAC signature
- يمنع التنفيذ المزيف

### 2. ✅ التحقق من is_executed
- يمنع التنفيذ المكرر للاستثمار
- مفيد في حالة استدعاء webhook عدة مرات

### 3. ⚠️ يجب إضافة: Rate Limiting
- لا يوجد rate limiting على endpoint إنشاء نية الدفع
- قد يؤدي إلى إساءة استخدام

### 4. ⚠️ يجب إضافة: التحقق من IP
- للـ webhook endpoint، يجب التحقق من IP addresses المسموح بها من Paymob

---

## 📊 ملخص التوصيات

### أولوية عالية (High Priority)

1. **إضافة آلية Refund** عند فشل تنفيذ الاستثمار
2. **التحقق من حالة الفرصة** مرة أخرى في `executeInvestment()`
3. **استخدام database locks** لمنع race conditions
4. **التحقق من مطابقة المبالغ** بين intention و webhook

### أولوية متوسطة (Medium Priority)

5. **منع نوايا الدفع المكررة**
6. **التحقق من تغير سعر السهم**
7. **تحسين معالجة الأخطاء** في `executeInvestment()`
8. **استخدام findOrFail()** بدلاً من `find()` مع معالجة أفضل

### أولوية منخفضة (Low Priority)

9. **إضافة rate limiting**
10. **التحقق من IP addresses** للـ webhook
11. **إضافة آلية retry** للأخطاء المؤقتة
12. **تحسين التوثيق** للكود

---

## 📝 ملاحظات إضافية

### 1. **جودة الكود**
- الكود بشكل عام منظم وجيد
- استخدام Dependency Injection مناسب
- فصل الاهتمامات واضح

### 2. **التسجيل (Logging)**
- التسجيل شامل ومفصل
- يساعد في تتبع المشاكل

### 3. **معالجة الأخطاء**
- بشكل عام جيدة
- لكن تحتاج تحسين في حالات فشل التنفيذ

### 4. **الأداء**
- لا توجد مشاكل أداء واضحة
- لكن يجب مراقبة الأداء في الإنتاج

---

## ✅ الخلاصة

الكود بشكل عام جيد ومنظم، لكن يحتاج إلى تحسينات في:

1. **معالجة حالات الفشل**: إضافة آلية refund
2. **التحقق من البيانات**: التحقق مرة أخرى قبل التنفيذ
3. **Race Conditions**: استخدام database locks
4. **الأمان**: إضافة rate limiting والتحقق من IP

يُفضل تطبيق التوصيات ذات الأولوية العالية أولاً قبل الانتقال إلى الإنتاج.

---

**تاريخ المراجعة:** $(date)
**المراجع:** AI Code Review Assistant




