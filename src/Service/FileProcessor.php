<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use ZipArchive;

/**
 * Contains all useful methods to process files and import them into database.
 */
class FileProcessor
{
    /**
     * FileProcessor constructor.
     */
    public function __construct(protected string $projectDir, protected EntityManagerInterface $em)
    {
    }

    /**
     * Counts how much line there is in a file.
     *
     * @param string $file
     *                     The path of the file we want to process.
     *
     * The number of lines in a file.
     */
    public function getLinesCount(string $file): int
    {
        $linecount = 0;

        // Will go through file by iterating on each line to save memory
        $handle = fopen($file, 'r');
        while (!feof($handle)) {
            fgets($handle);
            ++$linecount;
        }

        fclose($handle);

        return $linecount - 1;
    }

    /**
     * Downloads zip file from url, extracts files.
     *
     * @param string $url
     *                      The url from which we can recover the file
     * @param string $name
     *                      The name of the file to store
     * @param bool   $isZip
     *                      If the file you're getting is a zip and needs to be unzipped
     */
    public function getFiles(string $url, $name = 'file', $isZip = false): array
    {
        $ext = $isZip ? 'zip' : 'txt';

        $filePath = $this->projectDir . "/var/{$name}.$ext";

        $fileHandler = fopen($filePath, 'w+');

        $client = HttpClient::create(['timeout' => null, 'verify_peer' => false, 'verify_host' => false]);

        $response = $client->request('GET', $url);
        foreach ($client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }

        fclose($fileHandler);

        if (!$isZip) {
            return [$filePath];
        }

        $zip = new ZipArchive();

        $zip->open($filePath);
        $zip->extractTo($this->projectDir . "/var/$name");
        $files = [];
        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $files[] = $this->projectDir . "/var/$name/" . $zip->getNameIndex($i);
        }
        $zip->close();

        // Delete zip
        unlink($filePath);

        return $files;
    }

    public function getFile(string $url, string $name = 'file', bool $isZip = false): string
    {
        return $this->getFiles($url, $name, $isZip)[0];
    }
}
