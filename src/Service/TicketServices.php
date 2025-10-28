<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class TicketService
{
    private $session;
    private $ticketsFile;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
        $this->ticketsFile = __DIR__ . '/../../var/data/tickets.json';
        $this->initializeSampleTickets();
    }

    private function initializeSampleTickets(): void
    {
        $tickets = $this->getTickets();
        if (empty($tickets)) {
            $sampleTickets = [
                [
                    'id' => 'ticket-1',
                    'title' => 'Fix login page styling',
                    'description' => 'The login page buttons are not aligned properly on mobile devices.',
                    'status' => 'open',
                    'createdAt' => '2024-01-15T10:30:00Z',
                    'updatedAt' => '2024-01-15T10:30:00Z'
                ],
                [
                    'id' => 'ticket-2', 
                    'title' => 'Implement user profile page',
                    'description' => 'Users should be able to view and edit their profile information.',
                    'status' => 'in_progress',
                    'createdAt' => '2024-01-14T14:20:00Z',
                    'updatedAt' => '2024-01-16T09:15:00Z'
                ],
                [
                    'id' => 'ticket-3',
                    'title' => 'Add email notifications',
                    'description' => 'Send email notifications for important events like ticket assignments.',
                    'status' => 'closed',
                    'createdAt' => '2024-01-10T08:45:00Z',
                    'updatedAt' => '2024-01-12T16:30:00Z'
                ]
            ];
            $this->saveTickets($sampleTickets);
        }
    }

    private function getTickets(): array
    {
        if (!file_exists($this->ticketsFile)) {
            return [];
        }
        
        $content = file_get_contents($this->ticketsFile);
        return json_decode($content, true) ?? [];
    }

    private function saveTickets(array $tickets): void
    {
        $dir = dirname($this->ticketsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        file_put_contents($this->ticketsFile, json_encode($tickets, JSON_PRETTY_PRINT));
    }

    public function getAllTickets(): array
    {
        return $this->getTickets();
    }

    public function getTicketsByStatus(string $status): array
    {
        $tickets = $this->getTickets();
        return array_filter($tickets, fn($ticket) => $ticket['status'] === $status);
    }

    public function getTicketById(string $id): ?array
    {
        $tickets = $this->getTickets();
        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }
        return null;
    }

    public function createTicket(array $ticketData): array
    {
        $tickets = $this->getTickets();
        
        $newTicket = [
            'id' => 'ticket-' . time() . '-' . uniqid(),
            'title' => $ticketData['title'],
            'description' => $ticketData['description'] ?? '',
            'status' => $ticketData['status'],
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ];

        $tickets[] = $newTicket;
        $this->saveTickets($tickets);

        return $newTicket;
    }

    public function updateTicket(string $id, array $updates): ?array
    {
        $tickets = $this->getTickets();
        $updated = false;

        foreach ($tickets as &$ticket) {
            if ($ticket['id'] === $id) {
                $ticket = array_merge($ticket, $updates);
                $ticket['updatedAt'] = date('c');
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->saveTickets($tickets);
            return $this->getTicketById($id);
        }

        return null;
    }

    public function deleteTicket(string $id): bool
    {
        $tickets = $this->getTickets();
        $initialCount = count($tickets);

        $tickets = array_filter($tickets, fn($ticket) => $ticket['id'] !== $id);

        if (count($tickets) < $initialCount) {
            $this->saveTickets(array_values($tickets));
            return true;
        }

        return false;
    }

    public function getStats(): array
    {
        $tickets = $this->getTickets();
        $total = count($tickets);
        $open = count($this->getTicketsByStatus('open'));
        $inProgress = count($this->getTicketsByStatus('in_progress'));
        $closed = count($this->getTicketsByStatus('closed'));

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'closed' => $closed
        ];
    }
}