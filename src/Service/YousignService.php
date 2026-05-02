<?php

namespace App\Service;

use App\Entity\Convention;
use App\Repository\SignatureRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service d'intégration Yousign v3.
 *
 * Positionnement des champs de signature via Smart Anchors.
 * Insérer les balises suivantes dans le gabarit PDF de la convention
 * (texte blanc sur fond blanc pour les masquer visuellement) :
 *
 *   {{s1|signature|85|37}}   → étudiant(e)          (signataire 1)
 *   {{s2|signature|85|37}}   → responsable entreprise (signataire 2)
 *   {{s3|signature|85|37}}   → proviseur / école      (signataire 3)
 *
 * Format : {{sN|field_type|width_px|height_px}}
 * Ratio obligatoire width/height ≈ 2.3 (ex. 85×37, 184×80, 230×100)
 */
class YousignService
{
    private const SANDBOX_URL    = 'https://api-sandbox.yousign.app/v3';
    private const PRODUCTION_URL = 'https://api.yousign.app/v3';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly SignatureRepository  $signatureRepository,
        private readonly LoggerInterface      $logger,
        private readonly string               $apiKey,
        private readonly bool                 $sandbox = true,
    ) {}

    // -------------------------------------------------------------------------
    // Primitives API
    // -------------------------------------------------------------------------

    private function getBaseUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_URL : self::PRODUCTION_URL;
    }

    private function request(string $method, string $path, array $options = []): array
    {
        $url = $this->getBaseUrl() . $path;

        $options['headers'] = array_merge(
            ['Authorization' => 'Bearer ' . $this->apiKey, 'Accept' => 'application/json'],
            $options['headers'] ?? [],
        );

        $response   = $this->httpClient->request($method, $url, $options);
        $statusCode = $response->getStatusCode();
        $content    = $response->toArray(false);

        if ($statusCode >= 400) {
            $this->logger->error('Yousign API error', [
                'status'   => $statusCode,
                'path'     => $path,
                'response' => $content,
            ]);
            throw new \RuntimeException(sprintf(
                'Yousign API error %d on %s: %s',
                $statusCode,
                $path,
                json_encode($content),
            ));
        }

        return $content;
    }

    // -------------------------------------------------------------------------
    // Étape 1 : Créer une demande de signature
    // -------------------------------------------------------------------------

    public function createSignatureRequest(string $name, string $deliveryMode = 'email'): array
    {
        return $this->request('POST', '/signature_requests', [
            'json' => [
                'name'          => $name,
                'delivery_mode' => $deliveryMode,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Étape 2 : Uploader le document PDF avec parse_anchors activé
    // -------------------------------------------------------------------------

    /**
     * @param bool $parseAnchors Activer la détection des Smart Anchors {{sN|…}} dans le PDF
     */
    public function addDocument(
        string $signatureRequestId,
        string $pdfPath,
        bool   $parseAnchors = true,
    ): array {
        if (!file_exists($pdfPath)) {
            throw new \InvalidArgumentException(sprintf('PDF introuvable : %s', $pdfPath));
        }

        $body = [
            'file'          => fopen($pdfPath, 'r'),
            'nature'        => 'signable_document',
            'parse_anchors' => $parseAnchors ? 'true' : 'false',
        ];

        $response = $this->httpClient->request(
            'POST',
            $this->getBaseUrl() . '/signature_requests/' . $signatureRequestId . '/documents',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept'        => 'application/json',
                ],
                'body' => $body,
            ],
        );

        $statusCode = $response->getStatusCode();
        $content    = $response->toArray(false);

        if ($statusCode >= 400) {
            $this->logger->error('Yousign addDocument error', [
                'status'   => $statusCode,
                'response' => $content,
            ]);
            throw new \RuntimeException(sprintf(
                'Yousign addDocument error %d: %s',
                $statusCode,
                json_encode($content),
            ));
        }

        $anchorsFound = $content['total_anchors'] ?? 0;
        $this->logger->info('Yousign: document uploadé', [
            'document_id'    => $content['id'],
            'total_anchors'  => $anchorsFound,
        ]);

        if ((int) $anchorsFound === 0) {
            throw new \RuntimeException(
                'Le PDF ne contient aucun ancre de signature détectable. '
                . 'Le gabarit doit contenir les balises : {{s1|signature|85|37}}, {{s2|signature|85|37}}, {{s3|signature|85|37}}.'
            );
        }

        return $content;
    }

    // -------------------------------------------------------------------------
    // Étape 3 : Ajouter un signataire (les champs sont gérés par les Smart Anchors)
    // -------------------------------------------------------------------------

    /**
     * Ajoute un signataire sans spécifier de champs manuels.
     * Les champs sont automatiquement assignés via les anchors {{sN|…}} du PDF.
     * L'ordre d'ajout détermine le N : 1er appel → s1, 2e → s2, etc.
     */
    public function addSigner(
        string $signatureRequestId,
        string $firstName,
        string $lastName,
        string $email,
        string $locale              = 'fr',
        string $signatureLevel      = 'electronic_signature',
        string $signatureAuthMode   = 'no_otp',
    ): array {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/signers', [
            'json' => [
                'info' => [
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'email'      => $email,
                    'locale'     => $locale,
                ],
                'signature_level'               => $signatureLevel,
                'signature_authentication_mode' => $signatureAuthMode,
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Étape 4 : Activer la demande (envoie les emails aux signataires)
    // -------------------------------------------------------------------------

    public function activateSignatureRequest(string $signatureRequestId): array
    {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/activate');
    }

    // -------------------------------------------------------------------------
    // Utilitaires
    // -------------------------------------------------------------------------

    public function getSignatureRequest(string $signatureRequestId): array
    {
        return $this->request('GET', '/signature_requests/' . $signatureRequestId);
    }

    public function cancelSignatureRequest(string $signatureRequestId, string $reason = 'Annulation de la convention'): array
    {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/cancel', [
            'json' => ['reason' => $reason],
        ]);
    }

    public function downloadSignedDocument(string $signatureRequestId, string $documentId): string
    {
        $url = $this->getBaseUrl()
            . '/signature_requests/' . $signatureRequestId
            . '/documents/' . $documentId
            . '/download';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => ['Authorization' => 'Bearer ' . $this->apiKey],
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException(sprintf(
                'Erreur téléchargement document signé : HTTP %d',
                $response->getStatusCode(),
            ));
        }

        return $response->getContent();
    }

    // -------------------------------------------------------------------------
    // Orchestration complète pour une Convention
    // -------------------------------------------------------------------------

    /**
     * Crée et active une demande de signature Yousign pour une convention.
     *
     * Prérequis PDF : le gabarit doit contenir les Smart Anchors suivants
     * (texte blanc sur fond blanc) :
     *   {{s1|signature|85|37}}  → étudiant(e)
     *   {{s2|signature|85|37}}  → responsable entreprise
     *   {{s3|signature|85|37}}  → proviseur / école
     */
    public function sendConventionSignatureRequest(Convention $convention, string $pdfPath): Convention
    {
        $student         = $convention->getStudent();
        $companyInfo     = $convention->getCompanyInfo();
        $schoolSignature = $this->signatureRepository->findOneBy([]);

        if (!$student) {
            throw new \LogicException('La convention n\'a pas d\'étudiant associé.');
        }
        if (!$companyInfo) {
            throw new \LogicException('La convention n\'a pas d\'informations entreprise.');
        }
        if (!$schoolSignature) {
            throw new \LogicException('Aucune configuration de signature école trouvée.');
        }

        // 1. Demande de signature
        $requestName     = sprintf('Convention de stage – %s %s', $student->getFirstName(), $student->getLastName());
        $signatureRequest = $this->createSignatureRequest($requestName);
        $requestId        = $signatureRequest['id'];

        $this->logger->info('Yousign: demande créée', ['id' => $requestId]);

        // 2. Document PDF — parse_anchors: true pour détecter {{s1|…}}, {{s2|…}}, {{s3|…}}
        $document   = $this->addDocument($requestId, $pdfPath);
        $documentId = $document['id'];

        // 3. Signataires (ordre : s1 → s2 → s3, doit correspondre aux anchors du PDF)
        $signers = [
            // s1 → {{s1|signature|85|37}} dans le PDF
            [
                'first_name' => $student->getFirstName(),
                'last_name'  => $student->getLastName(),
                'email'      => $student->getEmail(),
            ],
            // s2 → {{s2|signature|85|37}} dans le PDF
            [
                'first_name' => $companyInfo->getSupervisorFirstName() ?? $companyInfo->getResponsibleFirstName(),
                'last_name'  => $companyInfo->getSupervisorLastName()  ?? $companyInfo->getResponsibleLastName(),
                'email'      => $companyInfo->getSupervisorEmail()      ?? $companyInfo->getEmail(),
            ],
            // s3 → {{s3|signature|85|37}} dans le PDF
            [
                'first_name' => $schoolSignature->getPrenomProviseur(),
                'last_name'  => $schoolSignature->getNomProviseur(),
                'email'      => $schoolSignature->getEmailProviseur(),
            ],
        ];

        foreach ($signers as $signerData) {
            $this->addSigner(
                signatureRequestId: $requestId,
                firstName:          $signerData['first_name'],
                lastName:           $signerData['last_name'],
                email:              $signerData['email'],
            );
        }

        $this->logger->info('Yousign: signataires ajoutés', ['count' => count($signers)]);

        // 4. Activation → emails envoyés
        $this->activateSignatureRequest($requestId);

        $this->logger->info('Yousign: demande activée', ['id' => $requestId]);

        // 5. Mise à jour de la convention
        $convention->setYousignRequestId($requestId);
        $convention->setYousignDocumentId($documentId);
        $convention->setYousignStatus('ongoing');
        $convention->setStatus('pending_signature');

        return $convention;
    }

    // -------------------------------------------------------------------------
    // Synchronisation du statut
    // -------------------------------------------------------------------------

    public function syncConventionStatus(Convention $convention): Convention
    {
        $requestId = $convention->getYousignRequestId();
        if (!$requestId) {
            throw new \LogicException('La convention n\'a pas de demande Yousign associée.');
        }

        $data          = $this->getSignatureRequest($requestId);
        $yousignStatus = $data['status'] ?? 'unknown';

        $convention->setYousignStatus($yousignStatus);

        if ($yousignStatus === 'done') {
            $convention->setStatus('signed');
            $convention->setSignedAt(new \DateTime());
        } elseif (in_array($yousignStatus, ['expired', 'cancelled'], true)) {
            $convention->setStatus('cancelled');
        }

        return $convention;
    }
}
