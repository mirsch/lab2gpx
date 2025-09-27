<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\AdventureLabDatabase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function sprintf;

class Lab2GpxController extends AbstractController
{
    public function __construct(
        private readonly AdventureLabDatabase $database,
    ) {
    }

    #[Route('/lab2gpx-data/by-uuid/{uuid}', methods: ['GET'])]
    public function getDatabaseDataByUuidAction(string $uuid): Response
    {
        $row = $this->database->findLabByUuid($uuid);
        if (! $row) {
            throw $this->createNotFoundException(sprintf('Nothing found for UUID "%s"', $uuid));
        }

        return $this->json($row);
    }

    #[Route('/lab2gpx-data/by-code/{code}', methods: ['GET'])]
    public function getDatabaseDataByCodeAction(string $code): Response
    {
        $row = $this->database->findLabByCode($code);
        if (! $row) {
            throw $this->createNotFoundException(sprintf('Nothing found for code "%s"', $code));
        }

        return $this->json($row);
    }
}
