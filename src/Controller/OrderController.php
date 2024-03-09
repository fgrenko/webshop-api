<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\OptionsResolver\OrderOptionsResolver;
use App\Repository\OrderProductRepository;
use App\Repository\OrderRepository;
use App\Repository\PriceModificatorRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Service\OrderService;
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
class OrderController extends AbstractController
{
    #[Required]
    public OrderRepository $orderRepository;
    #[Required]
    public OrderOptionsResolver $orderOptionsResolver;
    #[Required]
    public ValidatorInterface $validator;
    #[Required]
    public UserRepository $userRepository;
    #[Required]
    public OrderService $orderService;
    #[Required]
    public ProductRepository $productRepository;
    #[Required]
    public OrderProductRepository $orderProductRepository;
    #[Required]
    public PriceModificatorRepository $priceModificatorRepository;

    #[Route('/orders', name: 'orders', methods: ["GET"], format: "json")]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        return $this->json($orderRepository->findAll(), context: ['groups' => ['order']]);
    }

    #[Route('/orders/{id}', name: 'order_get', methods: ["GET"])]
    public function get(Order $order): JsonResponse
    {
        return $this->json($order, context: ['groups' => ['order']]);
    }

    #[Route('/orders', name: 'order_create', methods: ["POST"], format: "json")]
    public function create(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true);
            $fields = $this->orderOptionsResolver->configureCreateOptions()->resolve($requestBody);
            $userId = (int)$request->query->get("user_id");
            $user = $this->userRepository->find($userId);

            $order = new Order();

            $buyer = $this->userRepository->find($fields['buyer']);
            $order->setBuyer($buyer);

            $priceModificators = array_map(function ($priceModificatorName) {
                return $this->priceModificatorRepository->findOneBy(['name' => $priceModificatorName]);
            }, $fields['priceModificators']);

            $errors = $this->validator->validate($order);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            foreach ($fields['products'] as $sku => $quantity) {
                $orderProduct = new OrderProduct();
                $product = $this->productRepository->find($sku);
                $price = $this->productRepository->findPriceForUser($product, $user);
                $orderProduct
                    ->setProduct($product)
                    ->setQuantity($quantity)
                    ->setPrice($price)
                    ->setOrderItem($order);
                $this->orderProductRepository->add($orderProduct, false);
                $order->addOrderProduct($orderProduct);
            }

            $order->setTotalPrice($priceModificators);

            $this->orderRepository->add($order);
            return $this->json($order, status: Response::HTTP_CREATED, context: ['groups' => ['order']]);
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
    #[Route("/orders/{id}", "order_delete", methods: ["DELETE"], format: "json")]
    public function delete(Order $order): JsonResponse
    {
        $this->orderRepository->remove($order);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}


