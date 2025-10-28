<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketFormType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketsController extends AbstractController
{
    #[Route('/tickets', name: 'app_tickets')]
    public function index(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $status = $request->query->get('status', 'all');
        $create = $request->query->get('create') === 'true';

        $tickets = match ($status) {
            'open' => $ticketRepository->findBy(['user' => $user, 'status' => 'open'], ['createdAt' => 'DESC']),
            'in_progress' => $ticketRepository->findBy(['user' => $user, 'status' => 'in_progress'], ['createdAt' => 'DESC']),
            'closed' => $ticketRepository->findBy(['user' => $user, 'status' => 'closed'], ['createdAt' => 'DESC']),
            default => $ticketRepository->findBy(['user' => $user], ['createdAt' => 'DESC'])
        };

        $ticket = new Ticket();
        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUser($user);
            
            $entityManager->persist($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket created successfully!');

            return $this->redirectToRoute('app_tickets');
        }

        $counts = [
            'all' => $ticketRepository->count(['user' => $user]),
            'open' => $ticketRepository->count(['user' => $user, 'status' => 'open']),
            'in_progress' => $ticketRepository->count(['user' => $user, 'status' => 'in_progress']),
            'closed' => $ticketRepository->count(['user' => $user, 'status' => 'closed']),
        ];

        return $this->render('tickets/index.html.twig', [
            'tickets' => $tickets,
            'current_status' => $status,
            'counts' => $counts,
            'form' => $form->createView(),
            'show_form' => $create || $form->isSubmitted(),
        ]);
    }

    #[Route('/tickets/{id}/edit', name: 'app_tickets_edit')]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $ticket->getUser() !== $user) {
            $this->addFlash('error', 'You cannot edit this ticket.');
            return $this->redirectToRoute('app_tickets');
        }

        $form = $this->createForm(TicketFormType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Ticket updated successfully!');

            return $this->redirectToRoute('app_tickets');
        }

        return $this->render('tickets/edit.html.twig', [
            'form' => $form->createView(),
            'ticket' => $ticket,
        ]);
    }

    #[Route('/tickets/{id}/delete', name: 'app_tickets_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user || $ticket->getUser() !== $user) {
            $this->addFlash('error', 'You cannot delete this ticket.');
            return $this->redirectToRoute('app_tickets');
        }

        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();

            $this->addFlash('success', 'Ticket deleted successfully!');
        }

        return $this->redirectToRoute('app_tickets');
    }
}