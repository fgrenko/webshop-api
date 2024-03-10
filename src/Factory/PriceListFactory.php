<?php

namespace App\Factory;

use App\Entity\PriceList;
use App\Repository\PriceListRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<PriceList>
 *
 * @method        PriceList|Proxy                     create(array|callable $attributes = [])
 * @method static PriceList|Proxy                     createOne(array $attributes = [])
 * @method static PriceList|Proxy                     find(object|array|mixed $criteria)
 * @method static PriceList|Proxy                     findOrCreate(array $attributes)
 * @method static PriceList|Proxy                     first(string $sortedField = 'id')
 * @method static PriceList|Proxy                     last(string $sortedField = 'id')
 * @method static PriceList|Proxy                     random(array $attributes = [])
 * @method static PriceList|Proxy                     randomOrCreate(array $attributes = [])
 * @method static PriceListRepository|RepositoryProxy repository()
 * @method static PriceList[]|Proxy[]                 all()
 * @method static PriceList[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static PriceList[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static PriceList[]|Proxy[]                 findBy(array $attributes)
 * @method static PriceList[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static PriceList[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class PriceListFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->text(10),
            'price' => self::faker()->randomFloat(10,0,10000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(PriceList $priceList): void {})
        ;
    }

    protected static function getClass(): string
    {
        return PriceList::class;
    }
}
