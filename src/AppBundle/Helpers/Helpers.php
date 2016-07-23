<?php

namespace AppBundle\Helpers;

/**
 * Classe regroupant des fonctions utilitaires
 */
class Helpers
{

    /**
     * Transforme une chaîne de caractère en alias (pas d'accents, pas caractères spéciaux, minuscules)
     * @param string $string Chaîne de caractère
     * @return string
     */
    static function slugify($string)
    {
        // On transforme la chaîne en minuscules
        $string = mb_strtolower($string, 'UTF-8');
        // On supprime les accents et on remplace les espaces par des tirets
        $string = str_replace(
            array(
                'à', 'â', 'ä', 'á', 'ã', 'å',
                'î', 'ï', 'ì', 'í',
                'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
                'ù', 'û', 'ü', 'ú',
                'é', 'è', 'ê', 'ë', '&',
                'ç', 'ÿ', 'ñ',
                'À', 'Â', 'Ä', 'Á', 'Ã', 'Å',
                'Î', 'Ï', 'Ì', 'Í',
                'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø',
                'Ù', 'Û', 'Ü', 'Ú',
                'É', 'È', 'Ê', 'Ë',
                'Ç', 'Ÿ', 'Ñ',
                ' ', '.', '\''
            ),
            array(
                'a', 'a', 'a', 'a', 'a', 'a',
                'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u',
                'e', 'e', 'e', 'e', 'et',
                'c', 'y', 'n',
                'A', 'A', 'A', 'A', 'A', 'A',
                'I', 'I', 'I', 'I',
                'O', 'O', 'O', 'O', 'O', 'O',
                'U', 'U', 'U', 'U',
                'E', 'E', 'E', 'E',
                'C', 'Y', 'N',
                '-', '', '-'
            ),$string);
        return $string;
    }

}