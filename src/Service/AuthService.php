<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class AuthService
{
    private $requestStack;
    private $usersFile;
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->session = $requestStack->getSession();
        $this->usersFile = __DIR__ . '/../../var/data/users.json';
        
        // Initialize demo user
        $this->initializeDemoUser();
    }

    private function initializeDemoUser(): void
    {
        $users = $this->getUsers();
        $demoUserExists = array_filter($users, fn($user) => $user['email'] === 'user@example.com');
        
        if (empty($demoUserExists)) {
            $users[] = [
                'email' => 'user@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'name' => 'Demo User'
            ];
            $this->saveUsers($users);
        }
    }

    private function getUsers(): array
    {
        if (!file_exists($this->usersFile)) {
            return [];
        }
        
        $content = file_get_contents($this->usersFile);
        return json_decode($content, true) ?? [];
    }

    private function saveUsers(array $users): void
    {
        $dir = dirname($this->usersFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function login(string $email, string $password): bool
    {
        if (empty($email) || empty($password)) {
            return false;
        }

        $users = $this->getUsers();
        $user = array_filter($users, fn($u) => $u['email'] === $email);
        
        if (empty($user)) {
            return false;
        }

        $user = reset($user);
        
        if (password_verify($password, $user['password'])) {
            $this->session->set('user', [
                'email' => $user['email'],
                'name' => $user['name']
            ]);
            return true;
        }

        return false;
    }

    public function signup(string $email, string $password, string $name): bool
    {
        if (empty($email) || empty($password) || empty($name)) {
            return false;
        }

        if (strlen($password) < 6) {
            return false;
        }

        $users = $this->getUsers();
        
        // Check if user already exists
        $userExists = array_filter($users, fn($user) => $user['email'] === $email);
        if (!empty($userExists)) {
            return false;
        }

        // Add new user
        $users[] = [
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name
        ];
        
        $this->saveUsers($users);

        // Auto login
        $this->session->set('user', [
            'email' => $email,
            'name' => $name
        ]);

        return true;
    }

    public function logout(): void
    {
        $this->session->remove('user');
    }

    public function isAuthenticated(): bool
    {
        return $this->session->has('user');
    }

    public function getUser(): ?array
    {
        return $this->session->get('user');
    }
}