<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this
            ->setDefined("name")
            ->setAllowedTypes("name", "string")
            ->setDefined("description")
            ->setAllowedTypes("description", "string")
            ->setDefined("sku")
            ->setAllowedTypes("sku", "string")
            ->setDefined("price")
            ->setAllowedTypes("price", "float")
            ->setDefined("published")
            ->setAllowedTypes("published", "boolean");


        if ($isRequired) {
            $this
                ->setRequired("name")
                ->setRequired("sku")
                ->setRequired("price")
                ->setRequired("published");
        }
        return $this;
    }
}
