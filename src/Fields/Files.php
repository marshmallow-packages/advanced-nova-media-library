<?php

namespace Marshmallow\AdvancedNovaMediaLibrary\Fields;

/**
 * Class Files
 *
 * @package Marshmallow\AdvancedNovaMediaLibrary\Fields
 */
class Files extends Images
{
    protected $defaultValidatorRules = [];

    public $meta = ['type' => 'file'];

    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->hideFromIndex();
    }

    public function fullSize(): self
    {
        return $this->withMeta(['fullSize' => true]);
    }
}
