<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class SessionSecuritySubscriber implements EventSubscriberInterface
{
    private const SESSION_TIMEOUT_KEY = '_session_timeout';
    private const MAX_INACTIVITY = 1800; // 30 minutes in seconds

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        // Check if user is authenticated
        $token = $this->tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof User) {
            return;
        }

        // Skip for API routes
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        // Check if session timeout has passed
        $lastActivity = $session->get(self::SESSION_TIMEOUT_KEY);

        if ($lastActivity && (time() - $lastActivity > self::MAX_INACTIVITY)) {
            // Session expired due to inactivity
            $session->invalidate();
            $session->getFlashBag()->add('warning', 'Votre session a expiré en raison d\'inactivité. Veuillez vous reconnecter.');

            $event->setResponse(new RedirectResponse($this->getLoginPath($request)));
            return;
        }

        // Update last activity time
        $session->set(self::SESSION_TIMEOUT_KEY, time());
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $request = $event->getRequest();
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if (!$user instanceof User) {
            return;
        }

        $session = $request->getSession();

        // Set session timeout on login
        $session->set(self::SESSION_TIMEOUT_KEY, time());

        // Note: lastLoginAt and loginAttempts are already updated in Authenticator::onAuthenticationSuccess
        // We only update the session ID here for concurrent session detection
        $sessionId = $session->getId();
        $user->setLastSessionId($sessionId);

        $this->entityManager->flush();
    }

    private function getLoginPath(Request $request): string
    {
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser() instanceof User) {
            return '/login';
        }
        return '/login';
    }
}
