<?php

namespace Wame\LanguageModule\Gettext;

use ZipArchive;
use Nette\Utils\Finder;
use Wame\Utils\File\FileHelper;


class Download
{
    public static function lang($lang)
    {
        $path = PRIVATE_PATH . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . uniqid() . DIRECTORY_SEPARATOR;
        $fileName = $lang . '.zip';

        FileHelper::createDir($path);

        $zip = new ZipArchive();
        $zip->open($path . $fileName, ZipArchive::CREATE);

        foreach (Finder::findFiles($lang . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . '*.po')->from(BASE_PATH . DIRECTORY_SEPARATOR) as $key => $file) {
            $zip->addFile($file->getRealPath(), $lang . DIRECTORY_SEPARATOR . $file->getFileName());
        }

        $zip->close();

        header('Content-type: application/zip');
        header('Content-disposition: filename="' . $fileName . '"');
        header("Content-length: " . filesize($path . $fileName));
        readfile($path . $fileName);

        FileHelper::emptyDir($path, true);
    }

}
