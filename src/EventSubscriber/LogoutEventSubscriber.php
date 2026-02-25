<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        // Get the session
        $request = $event->getRequest();
        $session = $request->getSession();
        
        // Add flash message BEFORE invalidating the session
        if ($session instanceof Session) {
            $session->getFlashBag()->add('success', 'Vous avez été déconnecté avec succès.');
        }

        // Set logout response BEFORE invalidating session
        $event->setResponse(new RedirectResponse('/login'));
        
        // Now invalidate the session
        if ($session instanceof Session) {
            $session->invalidate();
        }
    }
}
