<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractListOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("user")->setAllowedTypes("user", "int");
        $this->setDefined("price")->setAllowedTypes("price", "float");
        $this->setDefined("product")->setAllowedTypes("product", "string");
        if ($isRequired) {
            $this->setRequired("user");
            $this->setRequired("price");
            $this->setRequired("product");
        }
        return $this;
    }
}
