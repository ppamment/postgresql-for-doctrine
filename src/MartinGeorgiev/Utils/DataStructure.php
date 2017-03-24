<?php

namespace MartinGeorgiev\Utils;

/**
 * Util class with helpers for working with PostgreSql data structures
 *
 * @since 0.9
 * @author Martin Georgiev <martin.georgiev@gmail.com>
 */
class DataStructure
{
    /**
     * This method supports only single-dimensioned text arrays and
     * relays on the default escaping strategy in PostgreSql (double quotes)
     *
     * @param string $postgresArray
     * @return array
     */
    public static function transformPostgresTextArrayToPHPArray($postgresArray)
    {
        $phpArray = str_getcsv(trim($postgresArray, '{}'));
        foreach ($phpArray as $i => $text) {
            if ($text === null) {
                unset($phpArray[$i]);
                break;
            }

            $isInteger = is_numeric($text) && ''.intval($text) === $text;
            if ($isInteger) {
                $phpArray[$i] = (int)$text;
                continue;
            }

            $isFloat = is_numeric($text) && ''.floatval($text) === $text;
            if ($isFloat) {
                $phpArray[$i] = (float)$text;
                continue;
            }

            $phpArray[$i] = str_replace('\"', '"', $text);
        }

        return $phpArray;
    }

    /**
     * This method relays on the default escaping strategy in PostgreSql (double quotes)
     *
     * @see https://stackoverflow.com/a/5632171/3425372 Kudos to jmz for the inspiration
     * @param array $phpArray
     * @return string
     */
    public static function transformPHPArrayToPostgresTextArray(array $phpArray)
    {
        $result = [];
        foreach ($phpArray as $text) {
            if (is_array($text)) {
                $result[] = self::transformPHPArrayToPostgresTextArray($text);
                continue;
            }

            if (is_numeric($text) || ctype_digit($text)) {
                $escapedText = $text;
            } elseif (empty($text)) {
                $escapedText = '';
            } else {
                $escapedText = '"' . str_replace('"', '\"', $text) . '"';
            }
            $result[] = $escapedText;
        }
        return '{' . implode(",", $result) . '}';
    }
}
