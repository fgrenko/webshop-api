<?php

namespace App\Controller;

use App\Entity\Category;
use App\OptionsResolver\CategoryOptionsResolver;
use App\Repository\CategoryRepository;
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


//TODO: add pagination
#[Route("/api")]
class CategoryController extends AbstractController
{
    #[Required]
    public CategoryRepository $categoryRepository;
    #[Required]
    public CategoryOptionsResolver $categoryOptionsResolver;
    #[Required]
    public ValidatorInterface $validator;

    #[Route('/categories', name: 'categories', methods: ["GET"], format: "json")]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        return $this->json($categoryRepository->findAll(), context: ['groups' => ['category']]);
    }

    #[Route('/categories/{id}', name: 'category_get', methods: ["GET"])]
    public function get(Category $category): JsonResponse
    {
        return $this->json($category, context: ['groups' => ['category']]);
    }

    #[Route('/categories', name: 'category_create', methods: ["POST"], format: "json")]
    public function create(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->categoryOptionsResolver->configureCreateOptions()->resolve($requestBody);

            $category = new Category();
            $category->setName($fields['name']);
            $category->setDescription($fields['description']);

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }


            $this->categoryRepository->add($category);

            return $this->json($category, status: Response::HTTP_CREATED, context: ['groups' => ['category']]);
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
    #[Route("/categories/{id}", "category_delete", methods: ["DELETE"], format: "json")]
    public function delete(Category $category): JsonResponse
    {
        $this->categoryRepository->remove($category);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/categories/{id}", "category_update", methods: ["PATCH", "PUT"], format: "json")]
    public function update(Category $category, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->categoryOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);
            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $category->setName($value);
                        break;
                    case "description":
                        $category->setDescription($value);
                        break;
                }
            }

            $errors = $this->validator->validate($category);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }
            $manager->flush();

            return $this->json($category, status: Response::HTTP_OK, context: ['groups' => ['category']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


