<?php

namespace Modules\Core\Service;

use Modules\Core\Entity\GlobalConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GlobalConfigService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_KEY_PREFIX = 'global_config_';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache
    ) {}

    /**
     * Get a configuration value by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $key;
        
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($key, $default) {
            $item->expiresAfter(self::CACHE_TTL);
            
            $config = $this->entityManager->getRepository(GlobalConfig::class)
                ->findOneBy(['configKey' => $key, 'isActive' => true]);
            
            if (!$config) {
                return $default;
            }
            
            return $config->getTypedValue();
        });
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, mixed $value, string $type = 'string', ?string $description = null): GlobalConfig
    {
        $config = $this->entityManager->getRepository(GlobalConfig::class)
            ->findOneBy(['configKey' => $key]);
        
        if (!$config) {
            $config = new GlobalConfig();
            $config->setConfigKey($key);
        }
        
        $config->setConfigType($type);
        $config->setTypedValue($value);
        $config->setDescription($description);
        $config->setUpdatedAt(new \DateTimeImmutable());
        
        $this->entityManager->persist($config);
        $this->entityManager->flush();
        
        // Clear cache
        $this->cache->delete(self::CACHE_KEY_PREFIX . $key);
        
        return $config;
    }

    /**
     * Get VAT rate
     */
    public function getVatRate(): float
    {
        return $this->get('global_vat_rate', 20.0);
    }

    /**
     * Set VAT rate
     */
    public function setVatRate(float $rate): GlobalConfig
    {
        return $this->set('global_vat_rate', $rate, 'float', 'Taux de TVA global de l\'application (en pourcentage)');
    }

    /**
     * Check if VAT is enabled
     */
    public function isVatEnabled(): bool
    {
        return $this->get('global_vat_enabled', true);
    }

    /**
     * Enable or disable VAT
     */
    public function setVatEnabled(bool $enabled): GlobalConfig
    {
        return $this->set('global_vat_enabled', $enabled, 'boolean', 'Activation/désactivation de la TVA globale');
    }

    /**
     * Calculate VAT amount
     */
    public function calculateVat(float $amount): float
    {
        if (!$this->isVatEnabled()) {
            return 0.0;
        }
        
        $vatRate = $this->getVatRate();
        return $amount * ($vatRate / 100);
    }

    /**
     * Calculate total with VAT
     */
    public function calculateTotalWithVat(float $amount): float
    {
        return $amount + $this->calculateVat($amount);
    }

    /**
     * Get all active configurations
     */
    public function getAll(): array
    {
        $configs = $this->entityManager->getRepository(GlobalConfig::class)
            ->findBy(['isActive' => true], ['configKey' => 'ASC']);
        
        $result = [];
        foreach ($configs as $config) {
            $result[$config->getConfigKey()] = $config->getTypedValue();
        }
        
        return $result;
    }

    /**
     * Initialize default configurations
     */
    public function initializeDefaults(): void
    {
        $defaults = [
            'global_vat_rate' => ['value' => 20.0, 'type' => 'float', 'description' => 'Taux de TVA global de l\'application (en pourcentage)'],
            'global_vat_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Activation/désactivation de la TVA globale'],
            'company_name' => ['value' => 'Mon Entreprise', 'type' => 'string', 'description' => 'Nom de l\'entreprise'],
            'company_address' => ['value' => '123 Rue de la Paix, 75001 Paris', 'type' => 'string', 'description' => 'Adresse de l\'entreprise'],
            'company_phone' => ['value' => '01 23 45 67 89', 'type' => 'string', 'description' => 'Téléphone de l\'entreprise'],
            'company_email' => ['value' => 'contact@monentreprise.fr', 'type' => 'string', 'description' => 'Email de l\'entreprise'],
            'invoice_prefix' => ['value' => 'FACT-', 'type' => 'string', 'description' => 'Préfixe pour les numéros de facture'],
            'currency' => ['value' => 'EUR', 'type' => 'string', 'description' => 'Devise par défaut'],
        ];

        foreach ($defaults as $key => $config) {
            $existing = $this->entityManager->getRepository(GlobalConfig::class)
                ->findOneBy(['configKey' => $key]);
            
            if (!$existing) {
                $this->set($key, $config['value'], $config['type'], $config['description']);
            }
        }
    }

    /**
     * Clear all configuration cache
     */
    public function clearCache(): void
    {
        $configs = $this->entityManager->getRepository(GlobalConfig::class)
            ->findBy(['isActive' => true]);
        
        foreach ($configs as $config) {
            $this->cache->delete(self::CACHE_KEY_PREFIX . $config->getConfigKey());
        }
    }
}
