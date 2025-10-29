<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function index(): Response
    {
        $features = [
            [
                'icon' => 'zap',
                'title' => 'Lightning Fast',
                'description' => 'Create and manage tickets in seconds with our intuitive interface',
            ],
            [
                'icon' => 'shield',
                'title' => 'Secure & Reliable',
                'description' => 'Your data is protected with industry-standard security measures',
            ],
            [
                'icon' => 'users',
                'title' => 'Team Collaboration',
                'description' => 'Work together seamlessly with real-time updates and notifications',
            ],
            [
                'icon' => 'check-circle',
                'title' => 'Track Progress',
                'description' => 'Monitor ticket status from creation to resolution with ease',
            ],
        ];

        return $this->render('landing/index.html.twig', [
            'features' => $features,
        ]);
    }
}