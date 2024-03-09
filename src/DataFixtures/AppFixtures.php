<?php

namespace App\DataFixtures;

use App\Entity\PriceModificator;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Service\Attribute\Required;
use const App\Entity\TYPE_DISCOUNT;
use const App\Entity\TYPE_VAT;

class AppFixtures extends Fixture
{
    #[Required]
    public UserPasswordHasherInterface $hasher;

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setFirstName("Marko");
        $user->setLastName("Markić");
        $user->setEmail("marko.markic@mail.com");
        $user->setPassword($this->hasher->hashPassword($user, "marko"));
        $user->setPhoneNumber("+3854453332455");
        $user->setAddress("Ilica 24");
        $user->setCity("Zagreb");
        $user->setCountry("Croatia");

        $manager->persist($user);

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
        $manager->flush();
    }
}
