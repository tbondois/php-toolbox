<?php

namespace TB\Toolbox;

/**
 * @author Thomas Bondois
 */
class File
{
    /**
     * @param string $file
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return array
     */
    public static function csv_to_array(string $file, string $delimiter = ",", string $enclosure = '"', string $escape = "\\") : array
    {
        $array = [];
        $content = file_get_contents($file);
        if ($content) {
            $array = str_getcsv($file, $delimiter, $enclosure, $escape);
        }
        return $array;
    }

    /**
     * @param array  $array
     * @param string $file
     * @return false|string|null
     */
    public static function array_to_csv(array $array, string $file = "php://output")
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen($file, 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

}
