<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCategoryOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("product")->setAllowedTypes("product", "string");
        $this->setDefined("category")->setAllowedTypes("category", "int");


        if ($isRequired) {
            $this->setRequired("product");
            $this->setRequired("category");
        }
        return $this;
    }
}
