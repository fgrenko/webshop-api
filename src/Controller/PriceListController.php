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


//TODO: add pagination
#[Route("/api")]
class PriceListController extends AbstractController
{
    #[Route('/price-lists', name: 'price-list', methods: ["GET"], format: "json")]
    public function index(PriceListRepository $priceListRepository): JsonResponse
    {
        return $this->json($priceListRepository->findAll(), context: ['groups' => ['price_list']]);
    }

    #[Route('/price-lists/{id}', name: 'price_list_get', methods: ["GET"])]
    public function get(PriceList $priceList): JsonResponse
    {
        return $this->json($priceList, context: ['groups' => ['price_list']]);
    }

    #[Route('/price-lists', name: 'price_list_create', methods: ["POST"], format: "json")]
    public function create(Request $request, ValidatorInterface $validator, PriceListRepository $priceListRepository, PriceListOptionsResolver $priceListOptionsResolver, ProductRepository $productRepository): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $priceListOptionsResolver->configureCreateOptions()->resolve($requestBody);

            $priceList = new PriceList();
            $priceList->setName($fields['name']);
            $priceList->setPrice($fields['price']);
            $product = $productRepository->find($fields['product']);
            $priceList->setProduct($product);
            $priceList->setType($fields['type']);

            $errors = $validator->validate($priceList);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $priceListRepository->add($priceList);

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
    public function delete(PriceList $priceList, PriceListRepository $priceListRepository): JsonResponse
    {
        $priceListRepository->remove($priceList);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route("/price-lists/{id}", "price_list_update", methods: ["PATCH", "PUT"], format: "json")]
    public function update(PriceList          $priceList, Request $request, PriceListOptionsResolver $priceListOptionsResolver,
                           ValidatorInterface $validator, EntityManagerInterface $manager,
                           ProductRepository  $productRepository): JsonResponse
    {
        $isPutMethod = $request->getMethod() === "PUT";
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $priceListOptionsResolver->configureCreateOptions($isPutMethod)->resolve($requestBody);
            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $priceList->setName($value);
                        break;
                    case "price":
                        $priceList->setPrice($value);
                        break;
                    case "product":
                        $product = $productRepository->find($value);
                        $priceList->setProduct($product);
                        break;
                    case "type":
                        $priceList->setType($value);
                        break;
                }
            }

            $errors = $validator->validate($priceList);
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


