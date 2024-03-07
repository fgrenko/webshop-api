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


#[Route("/api")]
class ProductCategoryController extends AbstractController
{
    //TODO: add pagination
    #[Route('/product-categories', name: 'product_categories', methods: ["GET"], format: "json")]
    public function index(ProductCategoryRepository $productCategoryRepository): JsonResponse
    {
        return $this->json($productCategoryRepository->findAll(), context: ['groups' => ['product_category']]);
    }

    #[Route('/product-categories/{id}', name: 'product_categories_get', methods: ["GET"], format: "json")]
    public function get(ProductCategory $productCategory): JsonResponse
    {
        return $this->json($productCategory);
    }

    #[Route('/product-categories', name: 'product_categories_create', methods: ["POST"], format: "json")]
    public function create(Request                        $request, ValidatorInterface $validator, ProductRepository $productRepository,
                           ProductCategoryOptionsResolver $productCategoryOptionsResolver,
                           CategoryRepository             $categoryRepository, ProductCategoryRepository $productCategoryRepository,
                           EntityManagerInterface         $manager): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $productCategoryOptionsResolver->configureCreateOptions()->resolve($requestBody);
            do {
                $productCategory = new ProductCategory();
                $category = $categoryRepository->find($fields['category']);
                $productCategory->setCategory($category);
                $product = $productRepository->find($fields['product']);
                $productCategory->setProduct($product);

                $errors = $validator->validate($productCategory);
                if (count($errors) > 0) {
                    throw new InvalidArgumentException((string)$errors);
                }
                $productCategoryRepository->add($productCategory, false);
                $category = $category->getParent();

            } while ($category && !$productCategoryRepository->findOneBy(["product" => $product, "category" => $category]));

            $manager->flush();

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
    public function delete(ProductCategory $productCategory, ProductCategoryRepository $productCategoryRepository): JsonResponse
    {
        $productCategoryRepository->remove($productCategory);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/product-categories/{id}", "product_categories_update", methods: ["PATCH", "PUT"], format: "json")]
    public function update(ProductCategory                $productCategory, Request $request,
                           ProductCategoryOptionsResolver $productCategoryOptionsResolver,
                           ValidatorInterface             $validator,
                           EntityManagerInterface         $manager, CategoryRepository $categoryRepository,
                           ProductCategoryRepository      $productCategoryRepository,
                           ProductRepository              $productRepository): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $productCategoryOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "category":
                        $category = $categoryRepository->find($value);
                        $productCategory->setCategory($category);
                        break;
                    case "product":
                        $product = $productRepository->find($value);
                        $productCategory->setProduct($product);
                        break;
                }
            }


            $errors = $validator->validate($productCategory);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $manager->flush();

            return $this->json($productCategory, status: Response::HTTP_OK, context: ['groups' => ['product_category']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


