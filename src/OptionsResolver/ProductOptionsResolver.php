<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");
        $this->setDefined("description")->setAllowedTypes("description", "string");
        $this->setDefined("sku")->setAllowedTypes("sku", "string");
        $this->setDefined("price")->setAllowedTypes("price", "float");
        $this->setDefined("published")->setAllowedTypes("published", "boolean");


        if ($isRequired) {
            $this->setRequired("name");
            $this->setRequired("sku");
            $this->setRequired("price");
            $this->setRequired("published");
        }
        return $this;
    }
}
