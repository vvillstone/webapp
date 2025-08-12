<?php

namespace App\EventListener;

use App\Service\InstallationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class InstallationListener implements EventSubscriberInterface
{
    private InstallationService $installationService;
    private RouterInterface $router;

    public function __construct(InstallationService $installationService, RouterInterface $router)
    {
        $this->installationService = $installationService;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Si l'application n'est pas installée et qu'on n'est pas déjà sur une page d'installation
        if (!$this->installationService->isInstalled() && !$this->isInstallRoute($path)) {
            // Rediriger vers l'installation
            $url = $this->router->generate('app_install');
            $response = new RedirectResponse($url);
            $event->setResponse($response);
        }
    }

    private function isInstallRoute(string $path): bool
    {
        $installRoutes = [
            '/install',
            '/install/database',
            '/install/admin',
            '/install/final',
            '/install/test-database'
        ];

        foreach ($installRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }
}
