<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\EventListener\LocaleListener;

class LocaleService extends LocaleListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Get the header language
        $headerLanguage = $request->headers->get('accept-language');

        // Set the locale based on the header language
        $request->setLocale($headerLanguage ?? 'pt');
    }
}
