<?php

namespace App\ApiPlatform\Filter;

trait FilterTrait
{

    /**
     * @param string $value
     * @return string
     */
    protected function cleanValue(string $value) : string
    {
        $value = trim(preg_replace('#\s+#', ' ', $value));
        $value = str_replace(" ","%",$value);

        setlocale(LC_ALL, 'fr_FR.utf8');
        $value = iconv('utf8', 'ascii//TRANSLIT', $value);

        return $value;
    }

}
