<?php

namespace App\Controller;

use App\Entity\ProductCategory;
use App\OptionsResolver\ProductCategoryOptionsResolver;
use App\Repository\CategoryRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
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
use Symfony\Contracts\Service\Attribute\Required;


#[Route("/api")]
class ProductCategoryController extends AbstractController
{
    #[Required]
    public ProductCategoryRepository $productCategoryRepository;
    #[Required]
    public ValidatorInterface $validator;
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public ProductCategoryOptionsResolver $productCategoryOptionsResolver;
    #[Required]
    public CategoryRepository $categoryRepository;
    #[Required]
    public EntityManagerInterface $manager;

    //TODO: add pagination
    #[Route('/product-categories', name: 'product_categories', methods: ["GET"], format: "json")]
    public function indexProductCategory(Request $request): JsonResponse
    {
        $limit = $request->query->get('limit');
        $page = $request->query->get('page');

        // Convert to int if not null, otherwise keep as null
        $limit = $limit !== null ? (int)$limit : 10;
        $page = $page !== null ? (int)$page : 1;

        return $this->json($this->productCategoryRepository->getPaginatedResults($page, $limit), context: ['groups' => ['product_category']]);
    }

    #[Route('/product-categories/{id}', name: 'product_categories_get', methods: ["GET"], format: "json")]
    public function getProductCategory(ProductCategory $productCategory): JsonResponse
    {
        return $this->json($productCategory);
    }

    #[Route('/product-categories', name: 'product_categories_create', methods: ["POST"], format: "json")]
    public function createProductCategory(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->productCategoryOptionsResolver->configureCreateOptions()->resolve($requestBody);
            $category = $this->categoryRepository->find($fields['category']);
            $product = $this->productRepository->find($fields['product']);
            do {
                $productCategory = new ProductCategory();
                $productCategory->setCategory($category);
                $productCategory->setProduct($product);
                $errors = $this->validator->validate($productCategory);
                if (count($errors) > 0) {
                    throw new InvalidArgumentException((string)$errors);
                }
                $this->productCategoryRepository->add($productCategory, false);
                $category = $category->getParent();
            } while ($category && !$this->productCategoryRepository->findOneBy(["product" => $product, "category" => $category]));
            $this->manager->flush();

            return $this->json($productCategory, status: Response::HTTP_CREATED, context: ['groups' => ['product_category']]);
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
    #[Route("/product-categories/{id}", "product_categories_delete", methods: ["DELETE"], format: "json")]
    public function deleteProductCategory(ProductCategory $productCategory): JsonResponse
    {
        $this->productCategoryRepository->remove($productCategory);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/product-categories/{id}", "product_categories_update", methods: ["PATCH", "PUT"], format: "json")]
    public function updateProductCategory(ProductCategory $productCategory, Request $request): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->productCategoryOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "category":
                        $category = $this->categoryRepository->find($value);
                        $productCategory->setCategory($category);
                        break;
                    case "product":
                        $product = $this->productRepository->find($value);
                        $productCategory->setProduct($product);
                        break;
                }
            }


            $errors = $this->validator->validate($productCategory);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $this->manager->flush();

            return $this->json($productCategory, status: Response::HTTP_OK, context: ['groups' => ['product_category']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


