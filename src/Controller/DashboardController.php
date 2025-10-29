<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SessionManager;
use App\Service\TicketService;
use App\Service\ToastService;

class DashboardController extends AbstractController
{
    private SessionManager $sessionManager;
    private TicketService $ticketService;
    private ToastService $toastService;

    public function __construct()
    {
        $this->sessionManager = new SessionManager();
        $this->ticketService = new TicketService();
        $this->toastService = new ToastService();
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            $this->toastService->error('Authentication required', 'Please log in to access the dashboard');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->sessionManager->getUser();
        $stats = $this->ticketService->getStats();
        $toasts = $this->toastService->getToasts();

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'stats' => $stats,
            'toasts' => $toasts
        ]);
    }

    #[Route('/dashboard/stats', name: 'app_dashboard_stats')]
    public function stats(): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->json(['error' => 'Not authenticated'], 401);
        }

        $stats = $this->ticketService->getStats();

        return $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}