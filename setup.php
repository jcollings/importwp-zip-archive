<?php

function iwp_zip_read_file_matching_ext($input_filepath)
{
    $ext = iwp_zip_archive_get_ext($input_filepath);

    switch ($ext) {
        case 'zip':

            $zip = new \ZipArchive();
            if (true !== $zip->open($input_filepath)) {
                return $input_filepath;
            }

            $file_found = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {

                $filename = $zip->getNameIndex($i);

                // TODO: we should be able to get file based on desired extension
                if (preg_match('/\.(xml|csv)$/', $filename) === 0 && $zip->numFiles > 1) {
                    continue;
                }

                $file_found = $filename;
                break;
            }

            if (!$file_found) {
                return $input_filepath;
            }

            $file_found_ext = iwp_zip_archive_get_ext($file_found);
            $output_filepath = $input_filepath . '.' . $file_found_ext;

            $file_contents = $zip->getFromName($file_found);
            if (!$file_contents) {
                return $input_filepath;
            }

            $zip->close();

            $output_filepath = iwp_zip_archive_unique_filename($output_filepath);
            file_put_contents($output_filepath, $file_contents);

            if ($input_filepath !== $output_filepath) {
                @unlink($input_filepath);
            }

            return $output_filepath;
        case 'gz':

            $output_filepath = iwp_zip_archive_get_output_filepath($input_filepath);
            $output_filepath = iwp_zip_archive_unique_filename($output_filepath);
            $sfp = gzopen($input_filepath, "rb");
            $fp = fopen($output_filepath, "w");

            while (!gzeof($sfp)) {
                $string = gzread($sfp, 4096);
                fwrite($fp, $string, strlen($string));
            }
            gzclose($sfp);
            fclose($fp);

            if ($input_filepath !== $output_filepath) {
                @unlink($input_filepath);
            }

            return $output_filepath;
    }

    return $input_filepath;
}

add_filter('iwp/importer/file_uploaded/file_path', 'iwp_zip_read_file_matching_ext', 10);

function iwp_zip_archive_get_ext($filepath)
{
    $file_parts = explode('.', basename($filepath));
    return $file_parts[count($file_parts) - 1];
}

function iwp_zip_archive_get_output_filepath($filepath)
{
    $file_parts = explode('.', $filepath);
    array_pop($file_parts);

    $last_part = $file_parts[count($file_parts) - 1];
    $matches = [];
    if (preg_match('/([^_]+)/', $last_part, $matches) !== false) {
        $file_parts[count($file_parts) - 1] = $matches[1];
    }

    return implode('.', $file_parts);
}

function iwp_zip_archive_unique_filename($filepath)
{
    $filename = basename($filepath);
    $dir = substr($filepath, 0, -strlen($filename));
    $filename = wp_unique_filename($dir, basename($filename));

    return $dir . '/' . $filename;
}
