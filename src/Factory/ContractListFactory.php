<?php

namespace App\Factory;

use App\Entity\ContractList;
use App\Repository\ContractListRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<ContractList>
 *
 * @method        ContractList|Proxy                     create(array|callable $attributes = [])
 * @method static ContractList|Proxy                     createOne(array $attributes = [])
 * @method static ContractList|Proxy                     find(object|array|mixed $criteria)
 * @method static ContractList|Proxy                     findOrCreate(array $attributes)
 * @method static ContractList|Proxy                     first(string $sortedField = 'id')
 * @method static ContractList|Proxy                     last(string $sortedField = 'id')
 * @method static ContractList|Proxy                     random(array $attributes = [])
 * @method static ContractList|Proxy                     randomOrCreate(array $attributes = [])
 * @method static ContractListRepository|RepositoryProxy repository()
 * @method static ContractList[]|Proxy[]                 all()
 * @method static ContractList[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static ContractList[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static ContractList[]|Proxy[]                 findBy(array $attributes)
 * @method static ContractList[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static ContractList[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class ContractListFactory extends ModelFactory
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
            'price' => self::faker()->randomFloat(10,0,10000),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(ContractList $contractList): void {})
        ;
    }

    protected static function getClass(): string
    {
        return ContractList::class;
    }
}
