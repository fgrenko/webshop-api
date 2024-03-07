<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceListOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");
        $this->setDefined("price")->setAllowedTypes("price", "float");
        $this->setDefined("product")->setAllowedTypes("product", "string");
        $this->setDefined("type")->setAllowedTypes("type", "int");
        if ($isRequired) {
            $this->setRequired("name");
            $this->setRequired("price");
            $this->setRequired("product");
            $this->setRequired("type");
        }
        return $this;
    }
}
