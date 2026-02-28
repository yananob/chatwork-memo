<?php

declare(strict_types=1);

namespace App;

/**
 * セキュリティ関連（CSRF, XSS）を担当するクラス
 */
class Security
{
    /**
     * CSRFトークンの生成
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRFトークンの検証
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * HTMLエスケープ (XSS対策)
     */
    public static function h(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
