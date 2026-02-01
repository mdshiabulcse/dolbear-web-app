<?php

namespace App\Helpers;

class SecurityLogger
{
    /**
     * Sanitize data for logging by masking sensitive fields
     *
     * @param array $data
     * @return array
     */
    public static function sanitize(array $data): array
    {
        $sensitiveKeys = [
            'card_type', 'card_no', 'bank_tran_id', 'card_issuer', 'card_brand',
            'cus_email', 'cus_phone', 'cus_add1', 'cus_add2', 'cus_city', 'cus_state',
            'ship_name', 'ship_add1', 'ship_add2', 'ship_phone', 'ship_city',
            'store_id', 'store_passwd', 'apiCredentials', 'store_password'
        ];

        $sanitized = $data;

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = self::mask($sanitized[$key]);
            }
        }

        // Mask email addresses specifically
        if (isset($sanitized['cus_email'])) {
            $sanitized['cus_email'] = self::maskEmail($sanitized['cus_email']);
        }

        return $sanitized;
    }

    /**
     * Mask sensitive string value
     *
     * @param string $value
     * @return string
     */
    private static function mask(string $value): string
    {
        if (empty($value)) {
            return '****';
        }

        $length = strlen($value);

        if ($length <= 4) {
            return '****';
        }

        if ($length <= 8) {
            return substr($value, 0, 2) . '****';
        }

        return substr($value, 0, 2) . '****' . substr($value, -2);
    }

    /**
     * Mask email address
     *
     * @param string $email
     * @return string
     */
    private static function maskEmail(string $email): string
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '****@****.***';
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return '****@****.***';
        }

        $name = $parts[0];
        $domain = $parts[1];

        $nameLength = strlen($name);
        if ($nameLength <= 3) {
            $maskedName = '****';
        } else {
            $maskedName = substr($name, 0, 2) . '****';
        }

        $domainParts = explode('.', $domain);
        $maskedDomain = '';

        if (count($domainParts) >= 2) {
            $tld = array_pop($domainParts);
            $mainDomain = implode('.', $domainParts);

            if (strlen($mainDomain) <= 4) {
                $maskedDomain = '****';
            } else {
                $maskedDomain = substr($mainDomain, 0, 2) . '****';
            }

            $maskedDomain .= '.' . $tld;
        } else {
            $maskedDomain = '****.***';
        }

        return $maskedName . '@' . $maskedDomain;
    }

    /**
     * Mask phone number
     *
     * @param string $phone
     * @return string
     */
    public static function maskPhone(string $phone): string
    {
        if (empty($phone)) {
            return '****';
        }

        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        $length = strlen($cleaned);

        if ($length <= 4) {
            return '****';
        }

        return substr($cleaned, 0, 2) . '****' . substr($cleaned, -2);
    }
}