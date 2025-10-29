<?php

namespace App\Service;

class ToastService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function addToast(string $title, string $description = '', string $type = 'success'): void
    {
        if (!isset($_SESSION['toasts'])) {
            $_SESSION['toasts'] = [];
        }

        $_SESSION['toasts'][] = [
            'id' => uniqid(),
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'timestamp' => time()
        ];
    }

    public function getToasts(): array
    {
        $toasts = $_SESSION['toasts'] ?? [];
        $_SESSION['toasts'] = []; // Clear after reading
        return $toasts;
    }

    // Helper methods
    public function success(string $title, string $description = ''): void
    {
        $this->addToast($title, $description, 'success');
    }

    public function error(string $title, string $description = ''): void
    {
        $this->addToast($title, $description, 'error');
    }

    public function warning(string $title, string $description = ''): void
    {
        $this->addToast($title, $description, 'warning');
    }

    public function info(string $title, string $description = ''): void
    {
        $this->addToast($title, $description, 'info');
    }
}