<?php

namespace App\Controller;

use App\Entity\Product;
use App\OptionsResolver\ProductOptionsResolver;
use App\Repository\CategoryRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route("/api")]
class ProductController extends AbstractController
{
    //TODO: add pagination
    /**
     * @throws BadRequestHttpException
     */
    #[Route('/products', name: 'products', methods: ["GET"], format: "json")]
    public function index(Request $request, ProductOptionsResolver $productOptionsResolver, ProductService $productService): JsonResponse
    {
        $options = $request->query->all();
        $options['user_id'] = $request->query->getInt('user_id');
        $resolvedOptions = $productOptionsResolver->configureIndexOptions()->resolve($options);
        $products = $productService->getPaginatedResults($resolvedOptions['user_id']);
        return $this->json($products, context: ['groups' => ['product']]);
    }


    #[Route('/products/{id}', name: 'product_get', methods: ["GET"], format: "json")]
    public function get(Product        $product, ProductOptionsResolver $productOptionsResolver,
                        ProductService $productService, Request $request): JsonResponse
    {
        $options = $request->query->all();
        $options['user_id'] = $request->query->getInt('user_id');
        $resolvedOptions = $productOptionsResolver->configureIndexOptions()->resolve($options);
        $productService->alterPrice($product, $resolvedOptions['user_id']);
        return $this->json($product, context: ['groups' => ['product']]);
    }

    #[Route('/products', name: 'product_create', methods: ["POST"], format: "json")]
    public function create(Request                $request, ValidatorInterface $validator, ProductRepository $productRepository,
                           ProductOptionsResolver $productOptionsResolver,
                           EntityManagerInterface $manager): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $productOptionsResolver->configureCreateOptions()->resolve($requestBody);

            $product = new Product();
            $product->setName($fields['name']);
            $product->setDescription($fields['description'] ?? null);
            $product->setPrice($fields['price']);
            $product->setSku($fields['sku']);
            $product->setPublished($fields['published']);

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }
            $productRepository->add($product, false);

            $manager->flush();

            return $this->json($product, status: Response::HTTP_CREATED, context: ['groups' => ['product']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (ORMException $e) {
            throw new ORMInvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route("/products/{id}", "product_delete", methods: ["DELETE"], format: "json")]
    public function delete(Product $product, ProductRepository $productRepository): JsonResponse
    {
        $productRepository->remove($product);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/products/{id}", "product_update", methods: ["PATCH", "PUT"], format: "json")]
    public function update(Product $product, Request $request, ProductOptionsResolver $productOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $manager, CategoryRepository $categoryRepository, ProductCategoryRepository $productCategoryRepository): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $productOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $product->setName($value);
                        break;
                    case "description":
                        $product->setDescription($value);
                        break;
                    case "sku":
                        $product->setSku($value);
                        break;
                    case "published":
                        $product->setPublished($value);
                        break;
                    case "price":
                        $product->setPrice($value);
                        break;
                }
            }


            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $manager->flush();

            return $this->json($product, status: Response::HTTP_OK, context: ['groups' => ['product']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


