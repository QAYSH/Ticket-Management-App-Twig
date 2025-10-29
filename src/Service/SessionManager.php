<?php

namespace App\Service;

class SessionManager
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function loginUser(string $email, string $name): void
    {
        $_SESSION['ticketapp_session'] = [
            'email' => $email,
            'name' => $name,
            'logged_in' => true
        ];
    }

    public function logoutUser(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION['ticketapp_session']) && 
               $_SESSION['ticketapp_session']['logged_in'] === true;
    }

    public function getUser(): ?array
    {
        return $_SESSION['ticketapp_session'] ?? null;
    }
}