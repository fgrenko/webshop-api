<?php

namespace App\Controller;

use App\Entity\ContractList;
use App\OptionsResolver\ContractListOptionsResolver;
use App\Repository\ContractListRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\PaginatorService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
class ContractListController extends AbstractController
{
    #[Required]
    public ValidatorInterface $validator;
    #[Required]
    public ContractListRepository $contractListRepository;
    #[Required]
    public ContractListOptionsResolver $contractListOptionsResolver;
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public UserRepository $userRepository;
    #[Required]
    public PaginatorService $paginatorService;

    #[Route('/contract-lists', name: 'contract_list', methods: ["GET"], format: "json")]
    public function indexContractList(Request $request): JsonResponse
    {
        list($page, $limit) = $this->paginatorService->getPageAndLimit($request);

        return $this->json($this->contractListRepository->getPaginatedResults($page, $limit), context: ['groups' => ['contract_list']]);
    }

    #[Route('/contract-lists/{id}', name: 'contract_list_get', methods: ["GET"])]
    public function getContractList(ContractList $contractList): JsonResponse
    {
        return $this->json($contractList, context: ['groups' => ['contract_list']]);
    }

    #[Route('/contract-lists', name: 'contract_list_create', methods: ["POST"], format: "json")]
    public function createContractList(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->contractListOptionsResolver->configureCreateOptions()->resolve($requestBody);
            $product = $this->productRepository->find($fields['product']);
            $user = $this->userRepository->find($fields['user']);

            if (!$user) {
                throw new \Exception("User doesn't exist.");
            }
            if (!$product) {
                throw  new \Exception("Product doesn't exist.");
            }

            $contractList = new ContractList();
            $contractList
                ->setPrice($fields['price'])
                ->setProduct($product)
                ->setUser($user);

            $errors = $this->validator->validate($contractList);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $this->contractListRepository->add($contractList);

            return $this->json($contractList, status: Response::HTTP_CREATED, context: ['groups' => ['contract_list']]);
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
    #[Route("/contract-lists/{id}", "contract_list_delete", methods: ["DELETE"], format: "json")]
    public function deleteContractList(ContractList $contractList): JsonResponse
    {
        $this->contractListRepository->remove($contractList);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/contract-lists/{id}", "contract_list_update", methods: ["PATCH", "PUT"], format: "json")]
    public function updateContractList(ContractList $contractList, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->contractListOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);
            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "price":
                        $contractList->setPrice($value);
                        break;
                    case "product":
                        $product = $this->productRepository->find($value);
                        $contractList->setProduct($product);
                        break;
                    case "user":
                        $user = $this->userRepository->find($value);
                        $contractList->setUser($user);
                        break;
                }
            }

            $errors = $this->validator->validate($contractList);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }
            $manager->flush();

            return $this->json($contractList, status: Response::HTTP_OK, context: ['groups' => ['contract_list']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


