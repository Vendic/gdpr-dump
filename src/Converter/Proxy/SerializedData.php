<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Proxy;

use Smile\Anonymizer\Converter\Helper\ArrayHelper;
use Smile\Anonymizer\Converter\ConverterInterface;

class SerializedData implements ConverterInterface
{
    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (empty($parameters['converters'])) {
            throw new \InvalidArgumentException('The serialized data converter requires a "converters" parameter.');
        }

        $this->converters = $parameters['converters'];
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $decoded = unserialize($value, true);

        if (!is_array($decoded)) {
            return $value;
        }

        /** @var ConverterInterface $converter */
        foreach ($this->converters as $path => $converter) {
            // Get the value
            $nestedValue = ArrayHelper::getPath($decoded, $path);
            if ($nestedValue === null) {
                continue;
            }

            // Format the value
            $nestedValue = $converter->convert($nestedValue, $context);

            // Replace the original value in the JSON by the converted value
            ArrayHelper::setPath($decoded, $path, $nestedValue);
        }


        $encoded = serialize($decoded);

        return $encoded;
    }
}
