<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryOptionsResolver extends OptionsResolver
{
    public function configureCreateOptions(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");
        $this->setDefined("description")->setAllowedTypes("name", "string");
        $this->setDefined("parent")->setAllowedTypes("parent", "int");
        if ($isRequired) {
            $this->setRequired("name");
        }
        return $this;
    }
}
