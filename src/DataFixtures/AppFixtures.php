<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\PriceModificator;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Factory\CategoryFactory;
use App\Factory\ContractListFactory;
use App\Factory\PriceListFactory;
use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Service\Attribute\Required;
use const App\Entity\ROLE_RETAIL;
use const App\Entity\ROLE_SERVICE;
use const App\Entity\TYPE_DISCOUNT;
use const App\Entity\TYPE_VAT;

class AppFixtures extends Fixture
{
    const SKU_ARRAY = ["ABC-12345-S-BL", "ABC-34322-S-BL", "ASC-4323-S-CG", "FSA-2322-S-BL", "LKJD-4323-S-SD",
        "SDSA-12321-S-LDS", "DKSL-123125-L-LS", "KFDD-9385-K-KJ", "SDLK-9382-KS-LSLS", "KFKS-3231-SF-S", "JFDH-4313-K-SD"];

    const CATEGORY_NAMES_ARRAY = ["Electronics", "Mobile Phones", "Fruit", "Frozen goods", "Meat", "Vegetables"];

    #[Required]
    public UserPasswordHasherInterface $hasher;

    #[Required]
    public CategoryRepository $categoryRepository;

    #[Required]
    public ProductRepository $productRepository;

    #[Required]
    public UserRepository $userRepository;

    public function load(ObjectManager $manager): void
    {
        $this->createCategories($manager);
        $this->createProducts();
        $this->createPriceModificators($manager);
        $this->assignCategoriesToProducts($manager);
        $this->createContractsAndPriceLists($manager);

        $manager->flush();
//        CategoryFactory::createMany(
//            6,
//            static function (int $i) {
//                return [
//                    'name' => self::CATEGORY_NAMES_ARRAY[$i - 1],
//                ];
//            }
//        );
//
//        ProductFactory::createMany(
//            11,
//            static function (int $i) {
//                return ['sku' => self::SKU_ARRAY[$i - 1], 'name' => "Product " . $i];
//            }
//        );
//
//        UserFactory::createMany(
//            2,
//            static function (int $i) {
//                return ['type' => $i == 1 ? ROLE_RETAIL : ROLE_SERVICE];
//            }
//        );
//
//        $vat = new PriceModificator();
//        $vat->setType(TYPE_VAT);
//        $vat->setPercentage(23);
//        $vat->setName("VAT");
//        $manager->persist($vat);
//
//        $discount = new PriceModificator();
//        $discount->setType(TYPE_DISCOUNT);
//        $discount->setName("SPRING");
//        $discount->setPercentage(5);
//        $manager->persist($discount);
//        $manager->flush();
//
//        $product = $this->productRepository->findOneBy(["sku" => self::SKU_ARRAY[1]]);
//        $category1 = $this->categoryRepository->findOneBy(['name' => self::CATEGORY_NAMES_ARRAY[0]]);
//        $category2 = $this->categoryRepository->findOneBy(['name' => self::CATEGORY_NAMES_ARRAY[1]]);
//        $user1 = $this->userRepository->findOneBy(['type' => ROLE_RETAIL]);
//
//        $category2->setParent($category1);
//        $manager->persist($category2);
//
//        foreach (self::SKU_ARRAY as $sku) {
//            $productItem = $this->productRepository->findOneBy(["sku" => $sku]);
//            $key = random_int(0, 5);
//            $categoryItem = $this->categoryRepository->findOneBy(["name" => self::CATEGORY_NAMES_ARRAY[$key]]);
//            $productCategory = new ProductCategory();
//            $productCategory->setProduct($product)->setCategory($categoryItem);
//            $manager->persist($productCategory);
//            if ($categoryItem->getId() == $category2->getId()) {
//                $productCategory1 = new ProductCategory();
//                $productCategory1->setProduct($productItem)->setCategory($category1);
//                $manager->persist($productCategory1);
//            }
//        }
//
//        ContractListFactory::createMany(
//            1,
//            static function () use ($product, $user1) {
//                return ['user' => $user1, 'product' => $product];
//            }
//        );
//
//        PriceListFactory::createMany(
//            1,
//            static function () use ($product, $user1) {
//                return ['userType' => ROLE_SERVICE, 'product' => $product];
//            }
//        );
//
//        $manager->flush();
    }

    private function createCategories(ObjectManager $manager): void
    {
        CategoryFactory::createMany(
            count(self::CATEGORY_NAMES_ARRAY),
            function (int $i) {
                return ['name' => self::CATEGORY_NAMES_ARRAY[$i - 1]];
            }
        );

        $category1 = $manager->getRepository(Category::class)->findOneBy(["name" => self::CATEGORY_NAMES_ARRAY[0]]);
        $category2 = $manager->getRepository(Category::class)->findOneBy(["name" => self::CATEGORY_NAMES_ARRAY[1]]);
        $category2->setParent($category1);
        $manager->persist($category2);
    }

    private function createProducts(): void
    {
        ProductFactory::createMany(
            count(self::SKU_ARRAY),
            function (int $i) {
                return ['sku' => self::SKU_ARRAY[$i - 1], 'name' => "Product " . ($i)];
            }
        );
    }

    private function createPriceModificators(ObjectManager $manager): void
    {
        $vat = new PriceModificator();
        $vat->setType(TYPE_VAT);
        $vat->setPercentage(23);
        $vat->setName("VAT");
        $manager->persist($vat);

        $discount = new PriceModificator();
        $discount->setType(TYPE_DISCOUNT);
        $discount->setName("SPRING");
        $discount->setPercentage(5);
        $manager->persist($discount);
    }

    private function assignCategoriesToProducts(ObjectManager $manager): void
    {
        foreach (self::SKU_ARRAY as $sku) {
            $product = $manager->getRepository(Product::class)->findOneBy(["sku" => $sku]);
            $randomInt = array_rand(self::CATEGORY_NAMES_ARRAY);
            $category = $manager->getRepository(Category::class)->findOneBy(["name" => self::CATEGORY_NAMES_ARRAY[$randomInt]]);
            $productCategory = new ProductCategory();
            $productCategory->setProduct($product)->setCategory($category);
            $manager->persist($productCategory);

            if ($randomInt == 1) {
                $productCategoryParent = new ProductCategory();
                $productCategoryParent->setCategory($category->getParent())->setProduct($product);
                $manager->persist($productCategoryParent);
            }
        }
    }

    private function createContractsAndPriceLists(ObjectManager $manager): void
    {
        $userRetail = UserFactory::createOne(['type' => ROLE_RETAIL]);
        $userService = UserFactory::createOne(['type' => ROLE_SERVICE]);
        $product = $manager->getRepository(Product::class)->findOneBy(["sku" => self::SKU_ARRAY[0]]);

        ContractListFactory::createOne(['user' => $userRetail, 'product' => $product]);
        PriceListFactory::createOne(['userType' => ROLE_SERVICE, 'product' => $product]);
    }
}
