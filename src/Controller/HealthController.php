<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Redis;

#[Route('/health')]
class HealthController extends AbstractController
{
    public function __construct(
        private Connection $connection,
        private ?Redis $redis = null
    ) {}

    #[Route('', name: 'health_check', methods: ['GET'])]
    public function health(): JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'version' => '1.0.0',
            'environment' => $this->getParameter('kernel.environment'),
            'checks' => []
        ];

        // Vérification de la base de données
        try {
            $this->connection->executeQuery('SELECT 1');
            $status['checks']['database'] = 'healthy';
        } catch (\Exception $e) {
            $status['checks']['database'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Vérification de Redis
        if ($this->redis) {
            try {
                $this->redis->ping();
                $status['checks']['redis'] = 'healthy';
            } catch (\Exception $e) {
                $status['checks']['redis'] = 'unhealthy';
                $status['status'] = 'degraded';
            }
        } else {
            $status['checks']['redis'] = 'not_configured';
        }

        // Vérification du cache
        try {
            $cache = $this->container->get('cache.app');
            $cache->get('health_check', function() {
                return 'ok';
            });
            $status['checks']['cache'] = 'healthy';
        } catch (\Exception $e) {
            $status['checks']['cache'] = 'unhealthy';
            $status['status'] = 'degraded';
        }

        // Vérification de l'espace disque
        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsage = (($diskTotal - $diskFree) / $diskTotal) * 100;
        
        $status['checks']['disk'] = [
            'status' => $diskUsage < 90 ? 'healthy' : 'warning',
            'usage_percent' => round($diskUsage, 2),
            'free_space_gb' => round($diskFree / 1024 / 1024 / 1024, 2)
        ];

        if ($diskUsage >= 90) {
            $status['status'] = 'degraded';
        }

        // Vérification de la mémoire
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        
        $status['checks']['memory'] = [
            'status' => 'healthy',
            'current_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_usage_mb' => round($memoryPeak / 1024 / 1024, 2),
            'limit' => $memoryLimit
        ];

        $httpCode = $status['status'] === 'healthy' ? 200 : 503;
        
        return new JsonResponse($status, $httpCode);
    }

    #[Route('/ready', name: 'health_ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        // Vérification que l'application est prête à recevoir du trafic
        try {
            // Vérification de la base de données
            $this->connection->executeQuery('SELECT 1');
            
            // Vérification du cache
            $cache = $this->container->get('cache.app');
            $cache->get('ready_check', function() {
                return 'ok';
            });

            return new JsonResponse([
                'status' => 'ready',
                'timestamp' => (new \DateTimeImmutable())->format('c')
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'not_ready',
                'error' => $e->getMessage(),
                'timestamp' => (new \DateTimeImmutable())->format('c')
            ], 503);
        }
    }

    #[Route('/live', name: 'health_live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        // Vérification simple que l'application est en vie
        return new JsonResponse([
            'status' => 'alive',
            'timestamp' => (new \DateTimeImmutable())->format('c')
        ], 200);
    }
}
