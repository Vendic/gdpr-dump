<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

class AnonymizeNumber extends AnonymizeText
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $isFirstCharacter = true;

        foreach (str_split($value) as $index => $char) {
            if (!is_numeric($char)) {
                $isFirstCharacter = true;
                continue;
            }

            if ($isFirstCharacter) {
                $isFirstCharacter = false;
                continue;
            }

            $value[$index] = '*';
        }

        return $value;
    }
}
