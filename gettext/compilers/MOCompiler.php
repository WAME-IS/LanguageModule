<?php

namespace Wame\LanguageModule\Gettext;

use Gettext\Translations;
use Wame\Utils\File\FileHelper;


class MOCompiler
{
    /** @var string */
    private $path;


    /**
     * Set path for find *.po files
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }


    /**
     * Get path for find *.po files
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * Run compile MO files
     */
    public function run()
    {
        $files = FileHelper::findFiles($this->getPath(), '*.po');

        foreach ($files as $fileName => $file) {
            $path = $file->getPath();
            $MOFileName = substr($file->getFileName(), 0, -3) . '.mo';

            $translations = Translations::fromPoFile($fileName);

            $translations->toMoFile($path . DIRECTORY_SEPARATOR . $MOFileName);
        }
    }

}
