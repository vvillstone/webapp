<?php

namespace Modules\Core\Controller;

use Modules\Core\Service\GlobalConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/config')]
class GlobalConfigController extends AbstractController
{
    public function __construct(
        private GlobalConfigService $globalConfigService
    ) {}

    #[Route('/vat', name: 'config_vat', methods: ['GET'])]
    public function getVatConfig(): JsonResponse
    {
        return $this->json([
            'vat_rate' => $this->globalConfigService->getVatRate(),
            'vat_enabled' => $this->globalConfigService->isVatEnabled(),
        ]);
    }

    #[Route('/vat/rate', name: 'config_vat_rate', methods: ['PUT'])]
    public function setVatRate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['rate']) || !is_numeric($data['rate'])) {
                return $this->json([
                    'error' => 'Le taux de TVA est requis et doit être numérique'
                ], 400);
            }
            
            $rate = (float) $data['rate'];
            
            if ($rate < 0 || $rate > 100) {
                return $this->json([
                    'error' => 'Le taux de TVA doit être entre 0 et 100'
                ], 400);
            }
            
            $config = $this->globalConfigService->setVatRate($rate);
            
            return $this->json([
                'success' => true,
                'message' => 'Taux de TVA mis à jour',
                'vat_rate' => $rate,
                'config' => [
                    'id' => $config->getId(),
                    'key' => $config->getConfigKey(),
                    'value' => $config->getConfigValue(),
                    'type' => $config->getConfigType(),
                    'description' => $config->getDescription(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour du taux de TVA',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/vat/enabled', name: 'config_vat_enabled', methods: ['PUT'])]
    public function setVatEnabled(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['enabled'])) {
                return $this->json([
                    'error' => 'Le paramètre enabled est requis'
                ], 400);
            }
            
            $enabled = (bool) $data['enabled'];
            $config = $this->globalConfigService->setVatEnabled($enabled);
            
            return $this->json([
                'success' => true,
                'message' => $enabled ? 'TVA activée' : 'TVA désactivée',
                'vat_enabled' => $enabled,
                'config' => [
                    'id' => $config->getId(),
                    'key' => $config->getConfigKey(),
                    'value' => $config->getConfigValue(),
                    'type' => $config->getConfigType(),
                    'description' => $config->getDescription(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour de l\'activation de la TVA',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/vat/calculate', name: 'config_vat_calculate', methods: ['POST'])]
    public function calculateVat(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['amount']) || !is_numeric($data['amount'])) {
                return $this->json([
                    'error' => 'Le montant est requis et doit être numérique'
                ], 400);
            }
            
            $amount = (float) $data['amount'];
            
            if ($amount < 0) {
                return $this->json([
                    'error' => 'Le montant doit être positif'
                ], 400);
            }
            
            $vatAmount = $this->globalConfigService->calculateVat($amount);
            $totalWithVat = $this->globalConfigService->calculateTotalWithVat($amount);
            $vatRate = $this->globalConfigService->getVatRate();
            $vatEnabled = $this->globalConfigService->isVatEnabled();
            
            return $this->json([
                'amount' => $amount,
                'vat_rate' => $vatRate,
                'vat_enabled' => $vatEnabled,
                'vat_amount' => $vatAmount,
                'total_with_vat' => $totalWithVat,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du calcul de la TVA',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/all', name: 'config_all', methods: ['GET'])]
    public function getAllConfig(): JsonResponse
    {
        try {
            $configs = $this->globalConfigService->getAll();
            
            return $this->json([
                'success' => true,
                'configs' => $configs,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des configurations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/set', name: 'config_set', methods: ['POST'])]
    public function setConfig(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['key']) || !isset($data['value'])) {
                return $this->json([
                    'error' => 'La clé et la valeur sont requises'
                ], 400);
            }
            
            $key = $data['key'];
            $value = $data['value'];
            $type = $data['type'] ?? 'string';
            $description = $data['description'] ?? null;
            
            $config = $this->globalConfigService->set($key, $value, $type, $description);
            
            return $this->json([
                'success' => true,
                'message' => 'Configuration mise à jour',
                'config' => [
                    'id' => $config->getId(),
                    'key' => $config->getConfigKey(),
                    'value' => $config->getConfigValue(),
                    'type' => $config->getConfigType(),
                    'description' => $config->getDescription(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour de la configuration',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/get/{key}', name: 'config_get', methods: ['GET'])]
    public function getConfig(string $key): JsonResponse
    {
        try {
            $value = $this->globalConfigService->get($key);
            
            return $this->json([
                'success' => true,
                'key' => $key,
                'value' => $value,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération de la configuration',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/initialize', name: 'config_initialize', methods: ['POST'])]
    public function initializeDefaults(): JsonResponse
    {
        try {
            $this->globalConfigService->initializeDefaults();
            
            return $this->json([
                'success' => true,
                'message' => 'Configurations par défaut initialisées',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'initialisation des configurations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/clear-cache', name: 'config_clear_cache', methods: ['POST'])]
    public function clearCache(): JsonResponse
    {
        try {
            $this->globalConfigService->clearCache();
            
            return $this->json([
                'success' => true,
                'message' => 'Cache des configurations vidé',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors du vidage du cache',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
