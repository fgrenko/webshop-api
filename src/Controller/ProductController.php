<?php

namespace App\Controller;

use App\Entity\Product;
use App\OptionsResolver\ProductOptionsResolver;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;


#[Route("/api")]
class ProductController extends AbstractController
{
    #[Required]
    public ProductOptionsResolver $productOptionsResolver;
    #[Required]
    public ProductService $productService;
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public ValidatorInterface $validator;

    //TODO: add pagination

    /**
     * @throws BadRequestHttpException
     */
    #[Route('/products', name: 'products', methods: ["GET"], format: "json")]
    public function index(Request $request): JsonResponse
    {
        $options = $request->query->all();
        $options['user_id'] = $request->query->getInt('user_id');
        $resolvedOptions = $this->productOptionsResolver->configureIndexOptions()->resolve($options);
        $products = $this->productService->getPaginatedResults($resolvedOptions['user_id']);
        return $this->json($products, context: ['groups' => ['product']]);
    }


    #[Route('/products/{id}', name: 'product_get', methods: ["GET"], format: "json")]
    public function get(Product $product, Request $request, UserRepository $userRepository): JsonResponse
    {
        $options = $request->query->all();
        $options['user_id'] = $request->query->getInt('user_id');
        $resolvedOptions = $this->productOptionsResolver->configureIndexOptions()->resolve($options);
        $user = $userRepository->find($resolvedOptions['user_id']);
        $product->setPrice($this->productRepository->findPriceForUser($product, $user));
        return $this->json($product, context: ['groups' => ['product']]);
    }

    #[Route('/products', name: 'product_create', methods: ["POST"], format: "json")]
    public function create(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->productOptionsResolver->configureCreateOptions()->resolve($requestBody);

            $product = new Product();
            $product->setName($fields['name']);
            $product->setDescription($fields['description'] ?? null);
            $product->setPrice($fields['price']);
            $product->setSku($fields['sku']);
            $product->setPublished($fields['published']);

            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }
            $this->productRepository->add($product);

            return $this->json($product, status: Response::HTTP_CREATED, context: ['groups' => ['product']]);
        } catch (Exception $e) {
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
    public function delete(Product $product): JsonResponse
    {
        $this->productRepository->remove($product);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/products/{id}", "product_update", methods: ["PATCH", "PUT"], format: "json")]
    public function update(Product $product, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->productOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);

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


            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $manager->flush();

            return $this->json($product, status: Response::HTTP_OK, context: ['groups' => ['product']]);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


