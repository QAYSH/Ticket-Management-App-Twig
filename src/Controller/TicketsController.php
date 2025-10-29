<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SessionManager;
use App\Service\TicketService;
use App\Service\ToastService;

class TicketsController extends AbstractController
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

    #[Route('/tickets', name: 'app_tickets')]
    public function index(Request $request): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            $this->toastService->error('Authentication required', 'Please log in to access tickets');
            return $this->redirectToRoute('app_login');
        }

        $user = $this->sessionManager->getUser();
        $tickets = $this->ticketService->getAllTickets();
        
        // Check if we should show create form
        $showCreateForm = $request->query->get('create') === 'true';

        $toasts = $this->toastService->getToasts();

        return $this->render('tickets/index.html.twig', [
            'user' => $user,
            'tickets' => $tickets,
            'showCreateForm' => $showCreateForm,
            'activeTab' => 'all',
            'toasts' => $toasts
        ]);
    }

    #[Route('/tickets/create', name: 'app_tickets_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            $this->toastService->error('Authentication required', 'Please log in to create tickets');
            return $this->redirectToRoute('app_login');
        }

        $title = $request->request->get('title', '');
        $description = $request->request->get('description', '');
        $status = $request->request->get('status', 'open');

        // Validate required fields
        if (empty(trim($title))) {
            $this->toastService->error('Validation error', 'Title is required');
            return $this->redirectToRoute('app_tickets', ['create' => 'true']);
        }

        if (!in_array($status, ['open', 'in_progress', 'closed'])) {
            $this->toastService->error('Validation error', 'Invalid status value');
            return $this->redirectToRoute('app_tickets', ['create' => 'true']);
        }

        try {
            $this->ticketService->createTicket($title, $description, $status);
            $this->toastService->success('Ticket created successfully', 'Your ticket has been created and is now visible in the list');
        } catch (\Exception $e) {
            $this->toastService->error('Failed to create ticket', 'Please try again later');
        }

        return $this->redirectToRoute('app_tickets');
    }

    #[Route('/tickets/{id}/edit', name: 'app_tickets_edit', methods: ['POST'])]
    public function edit(Request $request, string $id): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            $this->toastService->error('Authentication required', 'Please log in to edit tickets');
            return $this->redirectToRoute('app_login');
        }

        $title = $request->request->get('title', '');
        $description = $request->request->get('description', '');
        $status = $request->request->get('status', 'open');

        // Validate required fields
        if (empty(trim($title))) {
            $this->toastService->error('Validation error', 'Title is required');
            return $this->redirectToRoute('app_tickets');
        }

        if (!in_array($status, ['open', 'in_progress', 'closed'])) {
            $this->toastService->error('Validation error', 'Invalid status value');
            return $this->redirectToRoute('app_tickets');
        }

        // Check if ticket exists
        $existingTicket = $this->ticketService->getTicketById($id);
        if (!$existingTicket) {
            $this->toastService->error('Ticket not found', 'The ticket you are trying to edit does not exist');
            return $this->redirectToRoute('app_tickets');
        }

        try {
            $this->ticketService->updateTicket($id, $title, $description, $status);
            $this->toastService->success('Ticket updated successfully', 'Your changes have been saved');
        } catch (\Exception $e) {
            $this->toastService->error('Failed to update ticket', 'Please try again later');
        }

        return $this->redirectToRoute('app_tickets');
    }

    #[Route('/tickets/{id}/delete', name: 'app_tickets_delete', methods: ['POST'])]
    public function delete(string $id): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            $this->toastService->error('Authentication required', 'Please log in to delete tickets');
            return $this->redirectToRoute('app_login');
        }

        // Check if ticket exists
        $existingTicket = $this->ticketService->getTicketById($id);
        if (!$existingTicket) {
            $this->toastService->error('Ticket not found', 'The ticket you are trying to delete does not exist');
            return $this->redirectToRoute('app_tickets');
        }

        try {
            $this->ticketService->deleteTicket($id);
            $this->toastService->success('Ticket deleted successfully', 'The ticket has been permanently removed');
        } catch (\Exception $e) {
            $this->toastService->error('Failed to delete ticket', 'Please try again later');
        }

        return $this->redirectToRoute('app_tickets');
    }

    #[Route('/tickets/filter/{status}', name: 'app_tickets_filter')]
    public function filter(string $status): Response
    {
        if (!$this->sessionManager->isLoggedIn()) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->sessionManager->getUser();
        
        // Validate status
        if (!in_array($status, ['all', 'open', 'in_progress', 'closed'])) {
            $status = 'all';
        }

        $allTickets = $this->ticketService->getAllTickets();
        
        // Filter tickets if not 'all'
        $tickets = $status === 'all' 
            ? $allTickets 
            : $this->ticketService->getTicketsByStatus($status);

        $toasts = $this->toastService->getToasts();

        return $this->render('tickets/index.html.twig', [
            'user' => $user,
            'tickets' => $tickets,
            'showCreateForm' => false,
            'activeTab' => $status,
            'toasts' => $toasts
        ]);
    }
}