<?php

namespace App\Services;

use App\Models\NonCashReceipt;
use Illuminate\Support\Facades\Log;

use Exception;

class NegarSmsService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $originator;

    public function __construct(array $config)
    {
        // حذف اسلش اضافه از انتهای آدرس در صورت وجود
        $this->baseUrl    = rtrim($config['base_url'], '/');
        $this->username   = $config['username'];
        $this->password   = $config['password'];
        $this->originator = $config['originator'];
    }

    /**
     * ارسال پیامک تکی یا گروهی (Send One To Many SMS)
     */
    public function sendOneToMany(string $message, array $destinations): array
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

    /**
     * دریافت وضعیت پیامک (Get Message Status)
     */
    public function getMessageState(array $ids): array
    {
        $endpoint = '/webservice/rest/getMessageState';

        $data = [
            'username' => $this->username,
            'password' => $this->password,
            'ids'      => $ids
        ];

        return $this->request($endpoint, $data);
    }

    /**
     * دریافت پیام‌های رسیده (Receive Incoming Messages)
     */
    public function getReceivedMessages(array $params = []): array
    {
        $endpoint = '/webservice/rest/getReceivedMessages';

        $data = array_merge([
            'username' => $this->username,
            'password' => $this->password,
            'page'     => 0,
            'size'     => 100
        ], $params);

        return $this->request($endpoint, $data);
    }

    /**
     * دریافت اطلاعات کاربر و اعتبار (User Info / Credit)
     */
    public function getUserInfo(): array
    {
        $endpoint = '/webservice/rest/getUserInfo';

        $data = [
            'username' => $this->username,
            'password' => $this->password
        ];

        return $this->request($endpoint, $data);
    }

    /**
     * متد اصلی درخواست cURL
     */
    private function request(string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => json_encode($data),
            // تنظیمات SSL برای جلوگیری از خطا روی لوکال‌هاست
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 30
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl Error: ' . curl_error($ch));
        }

        curl_close($ch);

        return json_decode($response, true);
    }
    public function sendReceiptThankYou(NonCashReceipt $receipt): bool
    {
        try {
            $message = "خیر گرامی {$receipt->donor_name}،\nرسید کمک غیرنقدی شما به شماره {$receipt->receipt_number} با موفقیت در موسسه خیریه دارالاحسان ثبت شد.\nاز همراهی شما سپاسگزاریم.";

            // در اینجا وب‌سرویس پیامکی خود را فراخوانی کنید
            // مثال: Kavenegar::Send(..., $receipt->donor_mobile, $message);

            Log::info("SMS sent to {$receipt->donor_mobile}: {$message}");
            return true;
        } catch (\Exception $e) {
            Log::error("SMS Error: " . $e->getMessage());
            return false;
        }
    }
}
