<?php namespace Sanatorium\Sync\Traits;

use League\Csv\Reader;
use Excel;
use File;

trait DataParser {

    /**
     * @param null $file
     * @param array $configuration
     * @return array
     * @throws \Exception
     */
    public function getFileData($file = null, $configuration = [])
    {
        $path = $file->getPathname();

        $mime = File::mimeType($path);

        extract($configuration);

        $result = [];

        switch($mime) {

            // CSV
            case 'text/plain':

                $reader = Reader::createFromPath($path);
                $reader->setDelimiter($delimiter);
                $reader->setEnclosure($enclosure);
                $reader->setNewline($newline);

                $offset = 0;    // header line position

                foreach ($reader->fetchAssoc($offset) as $row) {
                    $result[] = $row;
                }

                $type = 'assoc';

                break;

            // XLS, XLS
            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/octet-stream':

                $reader = Excel::load($path);

                $result = $reader->toArray();

                $type = 'assoc';

                break;

            // XML
            case 'application/xml':

                $result = simplexml_load_string( file_get_contents($path), null, LIBXML_NOCDATA );

                $type = 'object';	# object

                break;

            default:

                throw new \Exception('Invalid mime type');

                break;
        }

        return [
            'data' => $result,
            'type' => $type,
            'mime' => $mime,
        ];
    }

}