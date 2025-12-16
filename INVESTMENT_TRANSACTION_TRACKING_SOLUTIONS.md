# حلول مشكلة تتبع معاملات الاستثمار المنفصلة

## المشكلة الحالية

عندما يشتري المستثمر أسهم في فرصة استثمارية على مرتين أو أكثر في نفس الفرصة من نفس نوع الاستثمار (myself أو authorize)، ما يحدث:

1. يتم دمج المشتريات في **record واحد** (`Investment`)
2. يتم جمع الأسهم في نفس الـ record
3. **المشكلة**: لا يمكن التمييز بين المشتريات المختلفة (transactions) سواء كبيانات أو كـ charts

### مثال على المشكلة:

```
المستثمر يشتري:
- 10 أسهم في 1 يناير 2024 (myself)
- 5 أسهم في 15 يناير 2024 (myself) ← يتم دمجها مع الأولى
- 8 أسهم في 1 فبراير 2024 (myself) ← يتم دمجها مع الأولى

النتيجة: Investment واحد بـ 23 سهم
المشكلة: لا يمكن معرفة متى تم شراء كل كمية من الأسهم
```

---

## الحلول المقترحة

### الحل 1: إنشاء جدول InvestmentTransaction منفصل (الأفضل) ⭐

#### المميزات:
- ✅ يحافظ على البنية الحالية (`Investment` كـ aggregate)
- ✅ يسمح بتتبع كل عملية شراء منفصلة
- ✅ يمكن عرض transactions منفصلة في charts وبيانات
- ✅ مرونة كاملة في التحليل والتقارير
- ✅ يمكن ربط كل transaction بـ payment intention/transaction

#### التنفيذ:

**1. Migration جديد:**

```php
Schema::create('investment_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('investment_id')->constrained('investments')->onDelete('cascade');
    $table->foreignId('investor_id')->constrained('investor_profiles')->onDelete('cascade');
    $table->foreignId('opportunity_id')->constrained('investment_opportunities')->onDelete('cascade');
    
    // Transaction details
    $table->integer('shares')->unsigned(); // عدد الأسهم في هذه العملية
    $table->decimal('share_price', 15, 2); // سعر السهم وقت الشراء
    $table->decimal('amount', 15, 2); // المبلغ (shares × share_price)
    $table->decimal('total_payment_required', 15, 2); // إجمالي المبلغ المطلوب (بما في ذلك رسوم الشحن)
    $table->enum('investment_type', ['myself', 'authorize']);
    
    // Payment tracking
    $table->foreignId('payment_intention_id')->nullable()->constrained('payment_intentions')->onDelete('set null');
    $table->foreignId('payment_transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
    
    // Timestamps
    $table->timestamps();
    $table->softDeletes();
    
    // Indexes
    $table->index(['investment_id', 'created_at']);
    $table->index(['investor_id', 'opportunity_id']);
});
```

**2. Model جديد:**

```php
class InvestmentTransaction extends Model
{
    protected $fillable = [
        'investment_id',
        'investor_id',
        'opportunity_id',
        'shares',
        'share_price',
        'amount',
        'total_payment_required',
        'investment_type',
        'payment_intention_id',
        'payment_transaction_id',
    ];
    
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }
    
    public function investor()
    {
        return $this->belongsTo(InvestorProfile::class, 'investor_id');
    }
    
    public function opportunity()
    {
        return $this->belongsTo(InvestmentOpportunity::class, 'opportunity_id');
    }
    
    public function paymentIntention()
    {
        return $this->belongsTo(PaymentIntention::class);
    }
}
```

**3. تحديث Investment Model:**

```php
// في Investment model
public function transactions()
{
    return $this->hasMany(InvestmentTransaction::class);
}
```

**4. تحديث InvestmentService:**

```php
protected function createNewInvestment(...): Investment
{
    // ... الكود الحالي ...
    
    $investment = $this->createInvestmentRecord(...);
    
    // إنشاء transaction منفصل
    InvestmentTransaction::create([
        'investment_id' => $investment->id,
        'investor_id' => $investor->id,
        'opportunity_id' => $opportunity->id,
        'shares' => $shares,
        'share_price' => $opportunity->share_price,
        'amount' => $amount,
        'total_payment_required' => $totalPaymentRequired,
        'investment_type' => $investmentType,
        'payment_intention_id' => $paymentIntentionId ?? null,
    ]);
    
    return $investment;
}

protected function updateExistingInvestment(...): Investment
{
    // ... الكود الحالي ...
    
    $this->updateInvestmentRecord(...);
    
    // إنشاء transaction جديد للشراء الإضافي
    InvestmentTransaction::create([
        'investment_id' => $existingInvestment->id,
        'investor_id' => $existingInvestment->investor_id,
        'opportunity_id' => $existingInvestment->opportunity_id,
        'shares' => $additionalShares,
        'share_price' => $opportunity->share_price,
        'amount' => $additionalAmount,
        'total_payment_required' => $additionalPaymentRequired,
        'investment_type' => $existingInvestment->investment_type,
        'payment_intention_id' => $paymentIntentionId ?? null,
    ]);
    
    return $existingInvestment;
}
```

**5. استخدام في Charts و Statistics:**

```php
// الحصول على transactions منفصلة للـ chart
$transactions = InvestmentTransaction::where('investor_id', $investorId)
    ->where('opportunity_id', $opportunityId)
    ->orderBy('created_at')
    ->get();

// Chart data
$chartData = $transactions->map(function($transaction) {
    return [
        'date' => $transaction->created_at->format('Y-m-d'),
        'shares' => $transaction->shares,
        'amount' => $transaction->amount,
    ];
});
```

---

### الحل 2: عدم دمج الاستثمارات (أبسط)

#### المميزات:
- ✅ أبسط في التنفيذ
- ✅ كل عملية شراء = record منفصل
- ✅ يمكن التمييز بين المشتريات بسهولة

#### العيوب:
- ❌ يغير البنية الحالية بشكل كبير
- ❌ قد يحتاج تعديلات في الكود الموجود
- ❌ قد يؤثر على التقارير والإحصائيات الحالية

#### التنفيذ:

**تعديل InvestmentService:**

```php
protected function getExistingInvestment(...): ?Investment
{
    // إزالة هذا المنطق - لا نبحث عن existing investment
    // return null; // دائماً ننشئ investment جديد
    return null; // أو إزالة المنطق تماماً
}

public function invest(...): Investment
{
    // دائماً ننشئ investment جديد
    return $this->createNewInvestment($investor, $opportunity, $shares, $investmentType, $skipWalletPayment);
}
```

**ملاحظة:** قد تحتاج تعديل queries التي تعتمد على وجود investment واحد لكل مستثمر/فرصة/نوع.

---

### الحل 3: إضافة JSON Field للتاريخ (أبسط تقنياً)

#### المميزات:
- ✅ لا يحتاج جدول جديد
- ✅ سهل التنفيذ
- ✅ يحافظ على البنية الحالية

#### العيوب:
- ❌ أقل مرونة في الاستعلامات
- ❌ صعب عمل queries معقدة على JSON
- ❌ قد يكون أبطأ في بعض الحالات

#### التنفيذ:

**1. Migration:**

```php
Schema::table('investments', function (Blueprint $table) {
    $table->json('transactions_history')->nullable()->after('total_payment_required');
});
```

**2. تحديث InvestmentService:**

```php
protected function createNewInvestment(...): Investment
{
    $investment = $this->createInvestmentRecord(...);
    
    // إضافة transaction للتاريخ
    $investment->transactions_history = [[
        'shares' => $shares,
        'amount' => $amount,
        'total_payment_required' => $totalPaymentRequired,
        'created_at' => now()->toDateTimeString(),
        'payment_intention_id' => $paymentIntentionId ?? null,
    ]];
    $investment->save();
    
    return $investment;
}

protected function updateExistingInvestment(...): Investment
{
    $this->updateInvestmentRecord(...);
    
    // إضافة transaction جديد للتاريخ
    $history = $existingInvestment->transactions_history ?? [];
    $history[] = [
        'shares' => $additionalShares,
        'amount' => $additionalAmount,
        'total_payment_required' => $additionalPaymentRequired,
        'created_at' => now()->toDateTimeString(),
        'payment_intention_id' => $paymentIntentionId ?? null,
    ];
    $existingInvestment->transactions_history = $history;
    $existingInvestment->save();
    
    return $existingInvestment;
}
```

**3. استخدام في Charts:**

```php
// في Investment model
public function getTransactionsHistoryAttribute($value)
{
    return $value ? json_decode($value, true) : [];
}

// استخدام
$transactions = $investment->transactions_history;
$chartData = collect($transactions)->map(function($transaction) {
    return [
        'date' => $transaction['created_at'],
        'shares' => $transaction['shares'],
        'amount' => $transaction['amount'],
    ];
});
```

---

## التوصية

**الحل 1 (InvestmentTransaction منفصل)** هو الأفضل لأنه:
1. يحافظ على البنية الحالية
2. يوفر مرونة كاملة في التحليل
3. يمكن ربطه بـ payment transactions
4. مناسب للـ charts والإحصائيات
5. قابل للتوسع في المستقبل

---

## خطوات التنفيذ المقترحة (الحل 1)

1. ✅ إنشاء migration للجدول الجديد
2. ✅ إنشاء InvestmentTransaction model
3. ✅ تحديث Investment model (إضافة relationship)
4. ✅ تحديث InvestmentService (إنشاء transactions)
5. ✅ تحديث PaymentWebhookService (ربط payment_intention_id)
6. ✅ تحديث Charts/Statistics controllers
7. ✅ تحديث Views (عرض transactions منفصلة)
8. ✅ كتابة tests

---

## ملاحظات إضافية

- يمكن إضافة `reference_number` أو `transaction_number` لكل transaction
- يمكن إضافة `notes` أو `metadata` لكل transaction
- يمكن إضافة `status` لكل transaction (pending, completed, cancelled)
- يمكن إضافة `refunded_at` إذا تم استرداد المبلغ

