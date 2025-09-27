<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\AdventureLabApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

class AdventureLabController extends AbstractController
{
    public function __construct(
        private readonly AdventureLabApiClient $adventureLabApiClient,
        private readonly KernelInterface $kernel,
    ) {
    }

    #[Route('/adventure-lab/adventure-id-by-smart-link/{link}', methods: ['GET'])]
    public function getAdventureIdBySmartLinkAction(string $link): Response
    {
        if ($this->kernel->getEnvironment() !== 'dev') {
            throw new AccessDeniedHttpException();
        }

        return $this->json($this->adventureLabApiClient->getAdventureIdBySmartLink($link));
    }

    #[Route('/adventure-lab/adventure/{id}', methods: ['GET'])]
    public function getAdventureAction(string $id): Response
    {
        if ($this->kernel->getEnvironment() !== 'dev') {
            throw new AccessDeniedHttpException();
        }

        return $this->json($this->adventureLabApiClient->getAdventureById($id, null));
    }
}
