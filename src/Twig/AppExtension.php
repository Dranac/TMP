<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('scientificNumberFormatter', [$this, 'scientificNumberFilter']),
        );
    }

    /**
     * @ToDo Find a way to deal with scientific number
     * @param $number
     * @return string
     */
    public function scientificNumberFilter($number)
    {
        if(preg_match('/\d+\.\d+E-\d+/', floatval($number))) {
            return rtrim(sprintf('%.20F', $number), '0');
        }

        return $number;
    }
}