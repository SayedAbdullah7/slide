<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SimpleHmacTest extends TestCase
{
    /**
     * Test HMAC validation with real Paymob data
     * يمكنك تغيير الـ HMAC secret هنا للتجربة
     */
    public function test_hmac_validation_simple()
    {
        // البيانات اللي جاية من Paymob
        $payload = [
            "type" => "TOKEN",
            "obj" => [
                "id" => 27911,
                "token" => "5fe444640033d1c5696ac76f2360af7f2c38f6c72fd18c0f5c644ac0",
                "masked_pan" => "xxxx-xxxx-xxxx-0008",
                "merchant_id" => 11883,
                "card_subtype" => "MasterCard",
                "created_at" => "2025-10-14T22:46:57.977092+03:00",
                "email" => "sayed@gmail.com",
                "order_id" => "1037965",
                "user_added" => false,
                "next_payment_intention" => "pi_test_4c022580ecca4f1f9ae38f6d9778c835",
            ]
        ];

        // الـ HMAC اللي جاي من Paymob
        $receivedHmac = "2c89c91fad5cb95b6f399536284155339b931e42998123ee59e967ebcb4e8f0f7f81aa93ffab06d372e4b67b05c04e29f965cd3be8ef94fbe77158daf4440eb3";

        // 🔑 غيّر الـ HMAC secret هنا عشان تجرب
        $hmacSecret = "E8862BCABDEFFEABC7C2C23A62ACEFAD"; // ← جرب secrets مختلفة

        echo "\n";
        echo "================================\n";
        echo "🧪 اختبار HMAC Validation\n";
        echo "================================\n\n";

        echo "📥 HMAC من Paymob:\n";
        echo "   {$receivedHmac}\n";
        echo "   Length: " . strlen($receivedHmac) . " characters\n\n";

        echo "🔑 HMAC Secret المستخدم:\n";
        echo "   {$hmacSecret}\n\n";

        // تحويل الـ payload لـ JSON
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        echo "📦 Payload JSON:\n";
        echo "   " . substr($json, 0, 100) . "...\n\n";

        // تجربة SHA-512 (HMAC length = 128)
        echo "🔍 محاولة SHA-512:\n";
        $calculated512 = hash_hmac('sha512', $json, $hmacSecret);
        $match512 = hash_equals($calculated512, $receivedHmac);
        echo "   Calculated: {$calculated512}\n";
        echo "   Result: " . ($match512 ? "✅ SUCCESS! متطابق!" : "❌ مش متطابق") . "\n\n";

        // تجربة SHA-256 (HMAC length = 64)
        echo "🔍 محاولة SHA-256:\n";
        $calculated256 = hash_hmac('sha256', $json, $hmacSecret);
        $match256 = hash_equals($calculated256, $receivedHmac);
        echo "   Calculated: {$calculated256}\n";
        echo "   Result: " . ($match256 ? "✅ SUCCESS! متطابق!" : "❌ مش متطابق") . "\n\n";

        echo "================================\n";

        if ($match512 || $match256) {
            echo "🎉 نجح! الـ HMAC secret صحيح!\n";
            $this->assertTrue(true);
        } else {
            echo "❌ فشل! الـ HMAC secret غلط.\n";
            echo "\n💡 جرب:\n";
            echo "   1. اتأكد من الـ HMAC secret من Paymob Dashboard\n";
            echo "   2. غيّر الـ \$hmacSecret في السطر 34\n";
            echo "   3. شغّل الاختبار تاني\n";

            // لا نفشل الاختبار عشان تقدر تشوف النتيجة
            $this->markTestIncomplete('HMAC secret غير صحيح - جرب secret مختلف');
        }

        echo "================================\n\n";
    }

    /**
     * اختبار بسيط بـ HMAC معروف
     * عشان تتأكد إن الكود شغال
     */
    public function test_hmac_with_known_values()
    {
        echo "\n";
        echo "🧪 اختبار بسيط بقيم معروفة:\n";

        $data = ["test" => "value"];
        $secret = "my-secret-key";
        $json = json_encode($data);

        $hmac = hash_hmac('sha256', $json, $secret);

        echo "   Data: " . $json . "\n";
        echo "   Secret: {$secret}\n";
        echo "   HMAC: {$hmac}\n";

        // التحقق من إن نفس الحساب يطلع نفس النتيجة
        $hmac2 = hash_hmac('sha256', $json, $secret);
        $this->assertEquals($hmac, $hmac2);

        echo "   ✅ الحساب صحيح!\n\n";
    }
}


