<?php
namespace App\Helpers;

class ContentFilter
{
    /**
     * Check if content contains prohibited patterns
     */
    public static function containsProhibitedContent(string $content): array
    {
        $violations = [];

        // Email pattern
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $content)) {
            $violations[] = 'email address';
        }

        // Phone number patterns (various formats)
        $phonePatterns = [
            '/\b\d{3}[-.\s]?\d{3}[-.\s]?\d{4}\b/', // 123-456-7890, 123.456.7890, 123 456 7890
            '/\b\d{10}\b/',                        // 1234567890
            '/\+\d{1,3}[-.\s]?\d{1,14}\b/',        // +1-234-567-8900
            '/\b0\d{9}\b/',                        // Ethiopian format: 0912345678
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'phone number';
                break;
            }
        }

        // Off-platform payment keywords
        $paymentKeywords = [
            'paypal', 'venmo', 'cashapp', 'cash app', 'zelle', 'western union',
            'moneygram', 'bitcoin', 'btc', 'crypto', 'bank transfer', 'wire transfer',
            'direct payment', 'pay outside', 'pay directly', 'telebirr', 'cbebirr',
            'mpesa', 'ebirr', 'hellocash',
        ];

        $lowerContent = strtolower($content);
        foreach ($paymentKeywords as $keyword) {
            if (strpos($lowerContent, $keyword) !== false) {
                $violations[] = 'off-platform payment reference';
                break;
            }
        }

        // Social media handles
        $socialPatterns = [
            '/@[A-Za-z0-9_]{1,15}\b/', // Twitter/X handle
            '/\bwhatsapp\b/i',
            '/\btelegram\b/i',
            '/\binstagram\b/i',
            '/\bfacebook\b/i',
            '/\bskype\b/i',
            '/\bdiscord\b/i',
        ];

        foreach ($socialPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $violations[] = 'social media contact';
                break;
            }
        }

        // URL patterns (to prevent external links for payment/contact)
        if (preg_match('/https?:\/\/[^\s]+/', $content)) {
            $violations[] = 'external link';
        }

        return $violations;
    }

    /**
     * Get a user-friendly message about violations
     */
    public static function getViolationMessage(array $violations): string
    {
        if (empty($violations)) {
            return '';
        }

        $violationList = implode(', ', array_unique($violations));

        return "Your message contains prohibited content ({$violationList}). " .
            "Please keep all communication and payments within the platform for your protection.";
    }
}
