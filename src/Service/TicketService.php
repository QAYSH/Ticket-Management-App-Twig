<?php

namespace App\Service;

class TicketService
{
    private string $ticketsFile;

    public function __construct()
    {
        // Use project directory for data storage
        $this->ticketsFile = __DIR__ . '/../../var/tickets.json';
        $this->initializeTicketsFile();
    }

    private function initializeTicketsFile(): void
    {
        // Ensure var directory exists
        $varDir = dirname($this->ticketsFile);
        if (!is_dir($varDir)) {
            mkdir($varDir, 0755, true);
        }

        if (!file_exists($this->ticketsFile)) {
            file_put_contents($this->ticketsFile, json_encode([]));
            chmod($this->ticketsFile, 0666);
        }
    }

    public function getAllTickets(): array
    {
        if (!file_exists($this->ticketsFile)) {
            $this->initializeTicketsFile();
        }
        
        $data = file_get_contents($this->ticketsFile);
        return json_decode($data, true) ?? [];
    }

    public function createTicket(string $title, string $description = '', string $status = 'open'): void
    {
        $tickets = $this->getAllTickets();

        $newTicket = [
            'id' => uniqid('ticket-'),
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ];

        $tickets[] = $newTicket;
        $this->saveTickets($tickets);
    }

    public function updateTicket(string $id, string $title, string $description, string $status): void
    {
        $tickets = $this->getAllTickets();

        foreach ($tickets as &$ticket) {
            if ($ticket['id'] === $id) {
                $ticket['title'] = $title;
                $ticket['description'] = $description;
                $ticket['status'] = $status;
                $ticket['updatedAt'] = date('c');
                break;
            }
        }

        $this->saveTickets($tickets);
    }

    public function deleteTicket(string $id): void
    {
        $tickets = $this->getAllTickets();
        $tickets = array_filter($tickets, function($ticket) use ($id) {
            return $ticket['id'] !== $id;
        });
        $this->saveTickets(array_values($tickets));
    }

    public function getTicketById(string $id): ?array
    {
        $tickets = $this->getAllTickets();

        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }

        return null;
    }

    private function saveTickets(array $tickets): void
    {
        file_put_contents($this->ticketsFile, json_encode($tickets, JSON_PRETTY_PRINT));
    }

    public function getTicketsByStatus(string $status): array
    {
        $tickets = $this->getAllTickets();
        return array_filter($tickets, function($ticket) use ($status) {
            return $ticket['status'] === $status;
        });
    }

    public function getStats(): array
    {
        $tickets = $this->getAllTickets();
        
        return [
            'total' => count($tickets),
            'open' => count($this->getTicketsByStatus('open')),
            'in_progress' => count($this->getTicketsByStatus('in_progress')),
            'closed' => count($this->getTicketsByStatus('closed'))
        ];
    }
}