<?php

namespace App\Utils;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// Manage upload and delete files in application
abstract class FileManager {

    /**
     * upload file
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string|null $oldFileName
     *
     * @return string
     *
     * @throws Exception
     */
    public static function uploadFile(UploadedFile $file, string $folder, string $oldFileName = null): string
    {
        $ext = $file->guessExtension() ?? 'bin';
        $filename = bin2hex(random_bytes(10)) . "." . $ext;

        $file->move($folder, $filename);

        static::deleteFile($folder, $oldFileName);

        return $filename;
    }

    /**
     * delete file
     *
     * @param string $folder
     * @param string|null $filename
     */
    public static function deleteFile(string $folder, ?string $filename): void
    {
        if ($filename && file_exists($folder . "/" . $filename)) {
            unlink($folder. "/" . $filename);
        }
    }
}