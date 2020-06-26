<?php

// global helper methods will go here
// they can be an alias of a Laravel method

// for loading legacy angular build files
function angularBuild($file)
{
    $name = explode('.', $file);
    $pattern =  public_path() . '/' . $name[0] . '*.' . $name[1];
    $path = glob($pattern);
    $path = explode('/', $path[0]);
    $path = array_slice($path, -2, 2, true);
    $result = '';
    foreach ($path as $part) {
        $result .= '/' . $part;
    }
    return $result;
}


// format an address into a string
function formatAddress($address)
{
        $addressString = isset($address->address_1) ? $address->address_1.' ' : '';
        $addressString .= isset($address->address_2) && $address->address_2 && $address->address_2 !== '' ? $address->address_2.' ' : '';
        $addressString .= isset($address->city) ? $address->city.', ' : '';
        $addressString .= isset($address->state) ? $address->state.' ' : '';
        $addressString .= isset($address->zip) ? $address->zip.'' : '';
        return $addressString;
}

// format phones for user readability
function formatPhone($phoneNumber)
{
    if ($phoneNumber) {
        return substr($phoneNumber, 0, 3)."-".substr($phoneNumber, 3, 3)."-".substr($phoneNumber, 6);
    }
}

// limit words in string
function limit_words($string, $word_limit)
{
    $words = explode(" ", $string);
    return implode(" ", array_splice($words, 0, $word_limit));
}

##########################
# delete images
##########################

function deleteImages($files)
{
    foreach ($files as $file) {
        $media = App\Models\Media::find($file->id);

        if (file_exists($media->xxs)) {
            unlink($media->xxs);
        }
        if (file_exists($media->xs)) {
            unlink($media->xs);
        }
        if (file_exists($media->sm)) {
            unlink($media->sm);
        }
        if (file_exists($media->md)) {
            unlink($media->md);
        }
        if (file_exists($media->lg)) {
            unlink($media->lg);
        }
        if (file_exists($media->xl)) {
            unlink($media->xl);
        }
    }
}

/*****************************
* Determine Media Type
******************************/
function determineMediaType($extension)
{
    $raster_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $vector_image_extensions = ['svg', 'tiff', 'psd', 'ai', 'eps'];
    $video_extensions = ['avchd', 'avi', 'flv', 'mpeg', 'mpg', 'mp4', 'wmv', 'mov', 'flv', 'rm', 'vob', 'swf'];
    $audio_extensions = ['wav', 'mp3', 'wma', 'flac', 'ogg', 'ra', 'ram', 'rm', 'mid', 'aiff', 'mpa', 'm4a', 'aif', 'iff'];
    $pdf_extensions = ['pdf'];
    $document_extensions = ['doc', 'docx', 'odt', 'pages', 'rtf', 'wpd', 'wps'];
    $spreadsheet_extensions = ['gnumeric', 'gnm', 'ods', 'xls', 'xlsx', 'xlsm', 'xlsb', 'csv'];
    $text_extensions = ['txt', 'log', 'msg', 'tex'];
    $presentation_extensions = ['key', 'ppt', 'pptx', 'odp'];
    $code_extensions = ['html', 'php', 'js', 'xml', 'json', 'c', 'class', 'cpp', 'cs', 'dtd', 'fla', 'h', 'java', 'lua', 'm', 'pl', 'py', 'sh', 'sln', 'swift', 'vcxproj', 'xcodeproj'];
    $database_extensions = ['odb', 'db', 'mdb', 'accdb', 'dbf', 'pdb', 'sql'];
    $archive_extensions = ['7z', 'cbr', 'deb', 'gz', 'pkg', 'rar', 'rpm', 'sitx', 'tar.gz', 'zip', 'zipx'];

    $category = 'File';
    if (in_array($extension, $raster_image_extensions)) {
        $category = 'Image';
    } elseif (in_array($extension, $vector_image_extensions)) {
        $category = 'Image media';
    } elseif (in_array($extension, $video_extensions)) {
        $category = 'Video';
    } elseif (in_array($extension, $audio_extensions)) {
        $category = 'Audio';
    } elseif (in_array($extension, $pdf_extensions)) {
        $category = 'PDF';
    } elseif (in_array($extension, $document_extensions)) {
        $category = 'Document';
    } elseif (in_array($extension, $spreadsheet_extensions)) {
        $category = 'Spreadsheet';
    } elseif (in_array($extension, $text_extensions)) {
        $category = 'Text';
    } elseif (in_array($extension, $presentation_extensions)) {
        $category = 'Presentation';
    } elseif (in_array($extension, $code_extensions)) {
        $category = 'Code';
    } elseif (in_array($extension, $database_extensions)) {
        $category = 'Database';
    } elseif (in_array($extension, $archive_extensions)) {
        $category = 'Archive';
    }

    return $category;
}

function isValidDate($date)
{
    $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
    return ($dateTime && $dateTime->format('Y-m-d') === $date);
}

function isValidDateTime($date)
{
    $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
    return ($dateTime and $dateTime->format('Y-m-d H:i:s') === $date);
}

/**
 * Sets an environment variable.
 * Can be sent an array of key value pairs e.g. setEnvVar(['key' => 'value'])
 * or just a single pair e.g. setEnvVar('key', 'value').
 *
 * @param string $key or array $key
 * @param string $value or null
 * @return array $data
 */
function setEnvVar($key, $value = null)
{
    if (App::environment('local') and auth()->user()->hasRole('Superadmin')) {
        // Check to see if it is an array of values or just a single pair
        if (is_array($key) and $value === null) {
            $data = $key;
        } else {
            $data = [$key => $value];
        }

        // read .env file
        $env = file_get_contents(base_path() . '/.env');
        // split string on every newline and write into array
        $env = preg_split("/\n/", $env);

        // Loop through given data
        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            // Loop through .env data
            foreach ($env as $envKey => $envValue) {
                // Turn the value into an array and stop after the first split
                $entry = explode("=", $envValue, 2);
                // Check to see if new key is in the actual .env key
                if ($entry[0] == $key) {
                    $env[$envKey] = $key . "=" . $value;
                }
            }
        }

        // Turn the array back to a string
        $env = implode("\n", $env);

        // Overwrite the .env with the new data
        file_put_contents(base_path() . '/.env', $env);

        return $data;
    }
}
