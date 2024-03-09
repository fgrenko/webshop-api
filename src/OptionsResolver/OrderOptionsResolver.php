<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("products")->setAllowedTypes("products", "int[]");
        $this->setDefined("buyer")->setAllowedTypes("buyer", "int");
        $this->setDefined("priceModificators")->setAllowedTypes("priceModificators", "string[]");
        if ($isRequired) {
            $this->setRequired("products");
            $this->setRequired("buyer");
            $this->setRequired("priceModificators");
        }
        return $this;
    }
}
