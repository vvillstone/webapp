<?php

namespace App\Controller;

use App\Service\InstallationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InstallController extends AbstractController
{
    private InstallationService $installationService;
    private ValidatorInterface $validator;

    public function __construct(InstallationService $installationService, ValidatorInterface $validator)
    {
        $this->installationService = $installationService;
        $this->validator = $validator;
    }

    #[Route('/install', name: 'app_install', methods: ['GET'])]
    public function index(): Response
    {
        // Vérifier si l'application est déjà installée
        if ($this->installationService->isInstalled()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('install/index.html.twig', [
            'step' => 1,
            'totalSteps' => 4,
            'systemCheck' => $this->installationService->getSystemCheck(),
        ]);
    }

    #[Route('/install/database', name: 'app_install_database', methods: ['GET', 'POST'])]
    public function database(Request $request): Response
    {
        if ($this->installationService->isInstalled()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            // Validation des données
            $constraints = new Assert\Collection([
                'db_host' => [new Assert\NotBlank(), new Assert\Length(['min' => 1])],
                'db_port' => [new Assert\NotBlank(), new Assert\Type(['type' => 'numeric']), new Assert\Range(['min' => 1, 'max' => 65535])],
                'db_name' => [new Assert\NotBlank(), new Assert\Length(['min' => 1])],
                'db_user' => [new Assert\NotBlank(), new Assert\Length(['min' => 1])],
                'db_password' => [new Assert\NotBlank()],
            ]);

            $violations = $this->validator->validate($data, $constraints);
            
            if (count($violations) === 0) {
                try {
                    $this->installationService->configureDatabase($data);
                    $this->addFlash('success', 'Configuration de la base de données réussie !');
                    return $this->redirectToRoute('app_install_admin');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la configuration de la base de données : ' . $e->getMessage());
                }
            } else {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            }
        }

        return $this->render('install/database.html.twig', [
            'step' => 2,
            'totalSteps' => 4,
        ]);
    }

    #[Route('/install/admin', name: 'app_install_admin', methods: ['GET', 'POST'])]
    public function admin(Request $request): Response
    {
        if ($this->installationService->isInstalled()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            
            // Validation des données
            $constraints = new Assert\Collection([
                'admin_email' => [new Assert\NotBlank(), new Assert\Email()],
                'admin_password' => [new Assert\NotBlank(), new Assert\Length(['min' => 8])],
                'admin_password_confirm' => [new Assert\NotBlank(), new Assert\EqualTo(['value' => $data['admin_password'] ?? ''])],
                'admin_firstname' => [new Assert\NotBlank(), new Assert\Length(['min' => 2])],
                'admin_lastname' => [new Assert\NotBlank(), new Assert\Length(['min' => 2])],
            ]);

            $violations = $this->validator->validate($data, $constraints);
            
            if (count($violations) === 0) {
                try {
                    $this->installationService->createAdminUser($data);
                    $this->addFlash('success', 'Compte administrateur créé avec succès !');
                    return $this->redirectToRoute('app_install_final');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la création du compte administrateur : ' . $e->getMessage());
                }
            } else {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            }
        }

        return $this->render('install/admin.html.twig', [
            'step' => 3,
            'totalSteps' => 4,
        ]);
    }

    #[Route('/install/final', name: 'app_install_final', methods: ['GET', 'POST'])]
    public function final(Request $request): Response
    {
        if ($this->installationService->isInstalled()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            try {
                $this->installationService->finalizeInstallation();
                $this->addFlash('success', 'Installation terminée avec succès !');
                return $this->redirectToRoute('app_home');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la finalisation : ' . $e->getMessage());
            }
        }

        return $this->render('install/final.html.twig', [
            'step' => 4,
            'totalSteps' => 4,
            'finalCheck' => $this->installationService->getFinalCheck(),
        ]);
    }

    #[Route('/install/test-database', name: 'app_install_test_database', methods: ['POST'])]
    public function testDatabase(Request $request): Response
    {
        $data = $request->request->all();
        
        try {
            $result = $this->installationService->testDatabaseConnection($data);
            return $this->json(['success' => true, 'message' => 'Connexion réussie !']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
