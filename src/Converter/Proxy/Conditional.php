<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use Smile\GdprDump\Converter\ConditionBuilder;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class Conditional implements ConverterInterface
{
    private string $condition;
    private ?ConverterInterface $ifTrueConverter = null;
    private ?ConverterInterface $ifFalseConverter = null;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters)
    {
        $input = (new ParameterProcessor())
            ->addParameter('condition', Parameter::TYPE_STRING, true)
            ->addParameter('if_true_converter', ConverterInterface::class)
            ->addParameter('if_false_converter', ConverterInterface::class)
            ->process($parameters);

        if (!isset($parameters['if_true_converter']) && !isset($parameters['if_false_converter'])) {
            throw new ValidationException(
                'The conditional converter requires a "if_true_converter" and/or "if_false_converter" parameter.'
            );
        }

        $conditionBuilder = new ConditionBuilder();
        $this->condition = $conditionBuilder->build($input->get('condition'));
        $this->ifTrueConverter = $input->get('if_true_converter');
        $this->ifFalseConverter = $input->get('if_false_converter');
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $result = (bool) eval($this->condition);

        if ($result) {
            if ($this->ifTrueConverter !== null) {
                $value = $this->ifTrueConverter->convert($value, $context);
            }
        } elseif ($this->ifFalseConverter !== null) {
            $value = $this->ifFalseConverter->convert($value, $context);
        }

        return $value;
    }
}
