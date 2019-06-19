<?php

namespace TB\Toolbox;

/**
 * @author    Thomas Bondois
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

    /**
     * Usage example:
     * $data = [ ... ];
     * File::download_send_headers("data_export_" . date("Y-m-d") . ".csv");
     * echo File::array_to_csv($data);
     *
     * @param string $file
     */
    public static function download_send_headers(string $file)
    {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$file}");
        header("Content-Transfer-Encoding: binary");
    }

}
