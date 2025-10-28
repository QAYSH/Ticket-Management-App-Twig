<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $stats = [
            'total' => $ticketRepository->count(['user' => $user]),
            'open' => $ticketRepository->count(['user' => $user, 'status' => 'open']),
            'in_progress' => $ticketRepository->count(['user' => $user, 'status' => 'in_progress']),
            'closed' => $ticketRepository->count(['user' => $user, 'status' => 'closed']),
        ];

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'user' => $user,
        ]);
    }
}