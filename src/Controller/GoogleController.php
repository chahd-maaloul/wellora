<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * Link to this controller to start the "Connect Google" process
     */
    #[Route('/connect/google', name: 'app_connect_google')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        // Redirect to Google
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile'], [
                'prompt' => 'consent', // Force consent to get refresh token
            ]);
    }

    /**
     * After going to Google, you're redirected back here
     * because this is the "redirect_route" you configured
     */
    #[Route('/connect/google/check', name: 'app_connect_google_check')]
    public function check(): void
    {
        // This method is never executed.
        // The OAuth2Authenticator handles the callback automatically.
        // If you want to handle it manually, remove this method.
        throw new \Exception('This should never be reached!');
    }
}
