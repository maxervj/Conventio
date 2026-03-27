<?php

namespace App\Controller;

use App\Repository\ConventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Reçoit les webhooks Yousign pour mettre à jour le statut des conventions.
 *
 * Configurer dans l'interface Yousign :
 *   URL : https://votre-domaine.fr/webhooks/yousign
 *   Événements : signature_request.done, signature_request.expired, signature_request.cancelled
 */
#[Route('/webhooks/yousign', name: 'webhook_yousign', methods: ['POST'])]
class YousignWebhookController extends AbstractController
{
    public function __construct(
        private readonly ConventionRepository $conventionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly string $webhookSecret,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Vérification de la signature HMAC si un secret est configuré
        if ($this->webhookSecret !== '') {
            $signature = $request->headers->get('X-Yousign-Signature-256', '');
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

            if (!hash_equals($expectedSignature, $signature)) {
                $this->logger->warning('Yousign webhook: signature invalide');
                return new JsonResponse(['error' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
            }
        }

        $payload = json_decode($request->getContent(), true);

        if (!$payload || !isset($payload['data']['signature_request']['id'])) {
            $this->logger->warning('Yousign webhook: payload invalide', ['body' => $request->getContent()]);
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $yousignRequestId = $payload['data']['signature_request']['id'];
        $eventName = $payload['event_name'] ?? '';
        $yousignStatus = $payload['data']['signature_request']['status'] ?? '';

        $this->logger->info('Yousign webhook reçu', [
            'event' => $eventName,
            'request_id' => $yousignRequestId,
            'status' => $yousignStatus,
        ]);

        $convention = $this->conventionRepository->findOneBy(['yousignRequestId' => $yousignRequestId]);

        if (!$convention) {
            $this->logger->notice('Yousign webhook: convention introuvable', ['yousign_id' => $yousignRequestId]);
            // On renvoie 200 pour éviter les retentatives Yousign
            return new JsonResponse(['status' => 'ignored']);
        }

        $convention->setYousignStatus($yousignStatus);

        match ($eventName) {
            'signature_request.done' => $this->handleDone($convention),
            'signature_request.expired' => $this->handleExpiredOrCancelled($convention, 'expired'),
            'signature_request.cancelled' => $this->handleExpiredOrCancelled($convention, 'cancelled'),
            default => null,
        };

        $this->entityManager->flush();

        return new JsonResponse(['status' => 'ok']);
    }

    private function handleDone(\App\Entity\Convention $convention): void
    {
        $convention->setStatus('signed');
        $convention->setSignedAt(new \DateTime());
        $this->logger->info('Convention signée via Yousign', ['id' => $convention->getId()]);
    }

    private function handleExpiredOrCancelled(\App\Entity\Convention $convention, string $reason): void
    {
        $convention->setStatus('cancelled');
        $this->logger->info('Convention annulée/expirée via Yousign', [
            'id' => $convention->getId(),
            'reason' => $reason,
        ]);
    }
}
