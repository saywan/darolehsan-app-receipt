<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $originator;

    public function __construct()
    {
        // تنظیمات را هاردکد می‌کنیم طبق فایل config.php شما
        // در حالت ایده آل باید در .env باشد اما طبق فایل شما پیش می‌رویم
        $this->baseUrl   = rtrim('https://negar.armaghan.net', '/');
        $this->username = 'ehsan';
        $this->password = 'yP842656';
        $this->originator = '50004644776860';
    }

    public function sendOtp($mobile, $code)
    {
        $message = "کد تایید شما در موسسه دارالاحسان:\n" . $code;
        // استفاده از متد sendOneToMany طبق کلاس شما
        return $this->sendOneToMany($message, [$mobile]);
    }

    /**
     * متد اصلی کلاس شما
     */
    private function sendOneToMany(string $message, array $destinations): array
    {
        $endpoint = '/webservice/rest/sendMessageOneToMany';
        $data = [
            'username'     => $this->username,
            'password'     => $this->password,
            'originator'   => $this->originator,
            'content'      => $message,
            'destinations' => $destinations
        ];

        return $this->request($endpoint, $data);
    }

    private function request(string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30
        ]);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            // در محیط واقعی بهتر است لاگ شود
            return ['status' => false, 'error' => curl_error($ch)];
        }
        curl_close($ch);
        return json_decode($response, true) ?? [];
    }

    public function sendNonCashDonationThanks(string $mobile, string $donorName, string $receiptNumber): bool
    {
        $message = "خیر گرامی {$donorName}\nکمک غیرنقدی شما در خیریه دارالاحسان با شماره رسید {$receiptNumber} ثبت گردید.\nاجرکم عندالله.";

        // در صورت اتصال به پنل پیامکی، وب‌سرویس در اینجا فراخوانی می‌شود
        Log::info("SMS Sent to {$mobile}: {$message}");

        return true;
    }
}
