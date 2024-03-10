<?php

namespace App\Controller;

use App\Entity\PriceList;
use App\OptionsResolver\PriceListOptionsResolver;
use App\Repository\PriceListRepository;
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

//TODO: add pagination
#[Route("/api")]
class PriceListController extends AbstractController
{
    #[Required]
    public PriceListRepository $priceListRepository;
    #[Required]
    public ValidatorInterface $validator;
    #[Required]
    public PriceListOptionsResolver $priceListOptionsResolver;
    #[Required]
    public ProductRepository $productRepository;

    #[Route('/price-lists', name: 'price-list', methods: ["GET"], format: "json")]
    public function indexPriceList(Request $request): JsonResponse
    {
        $limit = $request->query->get('limit');
        $page = $request->query->get('page');

        // Convert to int if not null, otherwise keep as null
        $limit = $limit !== null ? (int)$limit : 10;
        $page = $page !== null ? (int)$page : 1;

        return $this->json($this->priceListRepository->getPaginatedResults($page, $limit), context: ['groups' => ['price_list']]);
    }

    #[Route('/price-lists/{id}', name: 'price_list_get', methods: ["GET"])]
    public function getPriceList(PriceList $priceList): JsonResponse
    {
        return $this->json($priceList, context: ['groups' => ['price_list']]);
    }

    #[Route('/price-lists', name: 'price_list_create', methods: ["POST"], format: "json")]
    public function createPriceList(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->priceListOptionsResolver->configureCreateOptions()->resolve($requestBody);

            $priceList = new PriceList();
            $priceList->setName($fields['name']);
            $priceList->setPrice($fields['price']);
            $product = $this->productRepository->find($fields['product']);
            $priceList->setProduct($product);
            $priceList->setType($fields['type']);

            $errors = $this->validator->validate($priceList);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $this->priceListRepository->add($priceList);

            return $this->json($priceList, status: Response::HTTP_CREATED, context: ['groups' => ['price_list']]);
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
    #[Route("/price-lists/{id}", "price_list_delete", methods: ["DELETE"], format: "json")]
    public function deletePriceList(PriceList $priceList): JsonResponse
    {
        $this->priceListRepository->remove($priceList);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/price-lists/{id}", "price_list_update", methods: ["PATCH", "PUT"], format: "json")]
    public function updatePriceList(PriceList $priceList, Request $request, EntityManagerInterface $manager): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->priceListOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);
            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $priceList->setName($value);
                        break;
                    case "price":
                        $priceList->setPrice($value);
                        break;
                    case "product":
                        $product = $this->productRepository->find($value);
                        $priceList->setProduct($product);
                        break;
                    case "type":
                        $priceList->setType($value);
                        break;
                }
            }

            $errors = $this->validator->validate($priceList);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }
            $manager->flush();

            return $this->json($priceList, status: Response::HTTP_OK, context: ['groups' => ['price_list']]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}


