<?php

namespace App\Service;

use App\Entity\Convention;
use App\Enum\ApprovalStatusEnum;
use App\Repository\SignatureRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    public function createSignatureRequest(string $name, string $deliveryMode = 'email'): array
    {
        return $this->request('POST', '/signature_requests', [
            'json' => [
                'name'          => $name,
                'delivery_mode' => $deliveryMode,
            ],
        ]);
    }

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

        return $content;
    }

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

    public function addApprover(string $signatureRequestId, string $approverEmail): array
    {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/approvers', [
            'json' => ['email' => $approverEmail],
        ]);
    }

    public function activateSignatureRequest(string $signatureRequestId): array
    {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/activate');
    }

    public function approveSignatureRequest(string $signatureRequestId): array
    {
        return $this->request('POST', '/signature_requests/' . $signatureRequestId . '/approve');
    }

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

    public function sendConventionSignatureRequest(
        Convention $convention,
        string $pdfPath,
        ?bool $requireApproval = null,
        ?string $approverEmail = null,
    ): Convention {
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

        // 1. Créer la demande de signature
        $requestName     = sprintf('Convention de stage – %s %s', $student->getFirstName(), $student->getLastName());
        $signatureRequest = $this->createSignatureRequest($requestName);
        $requestId        = $signatureRequest['id'];

        $this->logger->info('Yousign: demande créée', ['id' => $requestId]);

        // 2. Ajouter le document PDF
        $document   = $this->addDocument($requestId, $pdfPath);
        $documentId = $document['id'];

        // 3. Ajouter les signataires
        $signers = [
            [
                'first_name' => $student->getFirstName(),
                'last_name'  => $student->getLastName(),
                'email'      => $student->getEmail(),
            ],
            [
                'first_name' => $companyInfo->getSupervisorFirstName() ?? $companyInfo->getResponsibleFirstName(),
                'last_name'  => $companyInfo->getSupervisorLastName()  ?? $companyInfo->getResponsibleLastName(),
                'email'      => $companyInfo->getSupervisorEmail()      ?? $companyInfo->getEmail(),
            ],
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

        // 4. Ajouter approbateur si nécessaire
        if ($requireApproval && $approverEmail) {
            $this->addApprover($requestId, $approverEmail);
            $convention->setApprovalStatus(ApprovalStatusEnum::PENDING);
            $this->logger->info('Yousign: approbateur ajouté', ['email' => $approverEmail]);
        } else {
            $convention->setApprovalStatus(ApprovalStatusEnum::APPROVED);
        }

        // 5. Activer la demande
        $this->activateSignatureRequest($requestId);
        $this->logger->info('Yousign: demande activée', ['id' => $requestId]);

        // 6. Mise à jour de la convention
        $convention->setSignatureRequestId($requestId);
        $convention->setYousignDocumentId($documentId);
        $convention->setYousignStatus('ongoing');
        $convention->setStatus('pending_signature');

        return $convention;
    }

    public function syncConventionStatus(Convention $convention): Convention
    {
        $requestId = $convention->getSignatureRequestId();
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
