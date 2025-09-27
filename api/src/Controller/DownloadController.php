<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Dto\OutputFormat;
use App\Model\Dto\Settings;
use App\Model\ExportGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use ZipStream\ZipStream;

use function basename;
use function count;
use function implode;
use function pathinfo;
use function sprintf;
use function str_contains;
use function strlen;

use const PATHINFO_FILENAME;

class DownloadController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly ExportGenerator $exportGenerator,
    ) {
    }

    #[Route('/download', methods: ['POST'])]
    public function downloadAction(Request $request): Response
    {
        try {
            $settings = $this->serializer->deserialize(
                $request->getContent(),
                Settings::class,
                'json',
            );
        } catch (NotNormalizableValueException $e) {
            return $this->json(
                [
                    'error' => 'Validation Error',
                    'errors' => [
                        $e->getPath() => [
                            sprintf('This value has to be "%s", "%s" given.', implode('|', $e->getExpectedTypes() ?? []), $e->getCurrentType() ?? ''),
                        ],
                    ],
                ],
                Response::HTTP_BAD_REQUEST,
            );
        } catch (NotEncodableValueException $e) {
            return $this->json(
                [
                    'error' => 'Invalid JSON.',
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        $violations = $this->validator->validate($settings);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return $this->json(
                [
                    'error' => 'Validation Error',
                    'errors' => $errors,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $file = $this->exportGenerator->generateFile($settings);

        switch ($settings->outputFormat) {
            case OutputFormat::GPX:
            case OutputFormat::ZIPPED_GPX:
                $fileName = 'lab2gpx.gpx';
                break;
            case OutputFormat::GPX_WPT:
            case OutputFormat::ZIPPED_GPX_WPT:
                $fileName = 'lab2gpx_wpt.gpx';
                break;
            case OutputFormat::CACHETURDOTNO:
                $fileName = 'lab2gpx_cacheturdotno.csv';
                break;
        }

        if (str_contains($settings->outputFormat->value, 'zipped')) {
            return new StreamedResponse(static function () use ($file, $fileName): void {
                $zip = new ZipStream(
                    outputName: pathinfo($fileName, PATHINFO_FILENAME) . '.zip',
                );
                $zip->addFile($fileName, $file);
                $zip->finish();
            });
        }

        $response = new Response($file);
        $response->headers->add(['Content-Disposition' => 'attachment; filename=' . basename($fileName)]);
        $response->headers->add(['Content-Type' => 'application/octet-stream']);
        $response->headers->add(['Content-Length' => strlen($file)]);

        return $response;
    }
}
