<?php

namespace App\EventListener;

use App\Entity\Signature;
use App\Service\EncryptionService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Signature::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Signature::class)]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Signature::class)]
class SignatureEncryptionListener
{
    public function __construct(
        private EncryptionService $encryptionService
    ) {
    }

    /**
     * Chiffre les données sensibles avant l'insertion en base
     */
    public function prePersist(Signature $signature, PrePersistEventArgs $event): void
    {
        $this->encryptSensitiveData($signature);
    }

    /**
     * Chiffre les données sensibles avant la mise à jour en base
     */
    public function preUpdate(Signature $signature, PreUpdateEventArgs $event): void
    {
        $this->encryptSensitiveData($signature);

        // Forcer la recalculation du changeset pour les champs chiffrés
        $em = $event->getObjectManager();
        $classMetadata = $em->getClassMetadata(Signature::class);
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $signature);
    }

    /**
     * Déchiffre les données sensibles après le chargement depuis la base
     */
    public function postLoad(Signature $signature, PostLoadEventArgs $event): void
    {
        $this->decryptSensitiveData($signature);
    }

    /**
     * Chiffre les données sensibles
     */
    private function encryptSensitiveData(Signature $signature): void
    {
        // Chiffrer la civilité du Proviseur
        if ($signature->getCiviliteProviseur() !== null) {
            $civiliteProviseur = $signature->getCiviliteProviseur();
            // Ne chiffrer que si ce n'est pas déjà chiffré
            if (!$this->encryptionService->isEncrypted($civiliteProviseur)) {
                $encrypted = $this->encryptionService->encrypt($civiliteProviseur);
                $signature->setCiviliteProviseur($encrypted);
            }
        }

        // Chiffrer la civilité du DDF
        if ($signature->getCiviliteDDF() !== null) {
            $civiliteDDF = $signature->getCiviliteDDF();
            if (!$this->encryptionService->isEncrypted($civiliteDDF)) {
                $encrypted = $this->encryptionService->encrypt($civiliteDDF);
                $signature->setCiviliteDDF($encrypted);
            }
        }

        // Chiffrer le téléphone du DDF
        if ($signature->getTelDDF() !== null) {
            $telDDF = $signature->getTelDDF();
            if (!$this->encryptionService->isEncrypted($telDDF)) {
                $encrypted = $this->encryptionService->encrypt($telDDF);
                $signature->setTelDDF($encrypted);
            }
        }
    }

    /**
     * Déchiffre les données sensibles
     */
    private function decryptSensitiveData(Signature $signature): void
    {
        // Déchiffrer la civilité du Proviseur
        if ($signature->getCiviliteProviseur() !== null) {
            $civiliteProviseur = $signature->getCiviliteProviseur();
            if ($this->encryptionService->isEncrypted($civiliteProviseur)) {
                try {
                    $decrypted = $this->encryptionService->decrypt($civiliteProviseur);
                    $signature->setCiviliteProviseur($decrypted);
                } catch (\Exception $e) {
                    // En cas d'erreur de déchiffrement, garder la valeur originale
                    // Log l'erreur si nécessaire
                }
            }
        }

        // Déchiffrer la civilité du DDF
        if ($signature->getCiviliteDDF() !== null) {
            $civiliteDDF = $signature->getCiviliteDDF();
            if ($this->encryptionService->isEncrypted($civiliteDDF)) {
                try {
                    $decrypted = $this->encryptionService->decrypt($civiliteDDF);
                    $signature->setCiviliteDDF($decrypted);
                } catch (\Exception $e) {
                    // En cas d'erreur, garder la valeur originale
                }
            }
        }

        // Déchiffrer le téléphone du DDF
        if ($signature->getTelDDF() !== null) {
            $telDDF = $signature->getTelDDF();
            if ($this->encryptionService->isEncrypted($telDDF)) {
                try {
                    $decrypted = $this->encryptionService->decrypt($telDDF);
                    $signature->setTelDDF($decrypted);
                } catch (\Exception $e) {
                    // En cas d'erreur, garder la valeur originale
                }
            }
        }
    }
}
