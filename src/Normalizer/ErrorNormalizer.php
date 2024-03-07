<?php

namespace App\Normalizer;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductCategory;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ErrorNormalizer implements NormalizerInterface
{
    public function normalize($object, string $format = null, array $context = []): array
    {
        return [
            'message' => $context['debug'] ? $object->getMessage() : 'An error occured',
            'status' => $object->getStatusCode(),
            'trace' => $context['debug'] ? $object->getTrace() : [],
        ];
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof FlattenException;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [
          Category::class,
          Product::class,
          ProductCategory::class,
        ];
    }
}
