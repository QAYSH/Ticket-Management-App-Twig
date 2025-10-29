<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SessionManager;
use App\Service\ToastService;

class AuthController extends AbstractController
{
    private SessionManager $sessionManager;
    private ToastService $toastService;

    public function __construct()
    {
        $this->sessionManager = new SessionManager();
        $this->toastService = new ToastService();
    }

    #[Route('/auth/login', name: 'app_login')]
    public function login(): Response
    {
        // Redirect if already logged in
        if ($this->sessionManager->isLoggedIn()) {
            $this->toastService->info('Already logged in', 'Welcome back!');
            return $this->redirectToRoute('app_dashboard');
        }
        
        $toasts = $this->toastService->getToasts();

        return $this->render('auth/login.html.twig', [
            'toasts' => $toasts
        ]);
    }

    #[Route('/auth/signup', name: 'app_signup')]
    public function signup(): Response
    {
        // Redirect if already logged in
        if ($this->sessionManager->isLoggedIn()) {
            $this->toastService->info('Already logged in', 'You are already signed in');
            return $this->redirectToRoute('app_dashboard');
        }
        
        $toasts = $this->toastService->getToasts();

        return $this->render('auth/signup.html.twig', [
            'toasts' => $toasts
        ]);
    }

    #[Route('/auth/login-handler', name: 'app_login_handler', methods: ['POST'])]
    public function loginHandler(Request $request): Response
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        // Demo authentication - accept demo credentials
        if ($email === 'user@example.com' && $password === 'password123') {
            $this->sessionManager->loginUser($email, 'Demo User');
            $this->toastService->success('Login successful', 'Welcome back to TickBase!');
            return $this->redirectToRoute('app_dashboard');
        }

        // If login fails
        $this->toastService->error('Login failed', 'Invalid credentials. Please try again.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/auth/signup-handler', name: 'app_signup_handler', methods: ['POST'])]
    public function signupHandler(Request $request): Response
    {
        $name = $request->request->get('name', '');
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');

        // Validate inputs
        if (empty(trim($name)) || empty(trim($email)) || empty(trim($password))) {
            $this->toastService->error('Signup failed', 'All fields are required');
            return $this->redirectToRoute('app_signup');
        }

        if (strlen($password) < 6) {
            $this->toastService->error('Signup failed', 'Password must be at least 6 characters');
            return $this->redirectToRoute('app_signup');
        }

        // Demo signup - automatically log in any user
        $this->sessionManager->loginUser($email, $name);
        $this->toastService->success('Account created successfully', 'Welcome to TickBase!');
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/auth/logout', name: 'app_logout')]
    public function logout(): Response
    {
        $this->sessionManager->logoutUser();
        $this->toastService->success('Logged out successfully', 'Hope to see you again soon!');
        return $this->redirectToRoute('app_landing');
    }
}