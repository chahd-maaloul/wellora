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
        // Get the session and clear all attributes
        $request = $event->getRequest();
        $session = $request->getSession();
        
        if ($session instanceof Session) {
            // Clear all session data
            $session->clear();
            
            // Invalidate the session cookie
            $session->invalidate();
        }

        // Get the firewall name and set logout response
        $event->setResponse(new RedirectResponse('/login'));
        
        // Add flash message
        $session->getFlashBag()->add('success', 'Vous avez été déconnecté avec succès.');
    }
}
