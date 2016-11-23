<?php

namespace Wame\LanguageModule\Gettext;

use Latte\RuntimeException;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Application\UI\ITemplateFactory;
use h4kuna\Gettext\Latte\FakeControl;
use h4kuna\Gettext\Latte\EmptyMacro;
use Latte\CompileException;
use Latte\Engine;
use SplFileInfo;
use Wame\Utils\File\FileHelper;


class LatteCompiler
{
    /** @var array */
    private $mask = ['*.latte'];

    /** @var Template */
    private $template;

    /** @var SplFileInfo[] */
    private $skippedFiles = [];

    /** @var SplFileInfo[] */
    private $files = [];

    /** @var string */
    private $temp;

    /** @var array */
    private $excludeDir = [];


    public function __construct(ITemplateFactory $templateFactory)
    {
        $this->template = $templateFactory->createTemplate(new FakeControl());
    }


    /**
     * Add file suffix
     *
     * @param string $mask
     */
    public function addMask($mask)
    {
        $this->mask[] = $mask;
    }


    /**
     * Exclude path or file
     *
     * @param string $path folder path or file
     * @return $this
     */
    public function addExclude($path)
    {
        $this->files = array_diff_key($this->files, $this->getFiles($path));

        return $this;
    }


    /**
     * Include path or file
     *
     * @param string $path folder path or file
     * @return $this
     */
    public function addInclude($path)
    {
        $this->files += $this->getFiles($path);

        return $this;
    }


    /**
     * Find files in path
     *
     * @param string $path
     * @return SplFileInfo[]
     */
    private function getFiles($path)
    {
        $fileInfo = new SplFileInfo($path);

        if ($fileInfo->isFile()) {
            return array($fileInfo->getRealPath() => $fileInfo);
        }

        $found = [];
        $finder = call_user_func_array('\Nette\Utils\Finder::findFiles', $this->mask);
        $finder->from($path);

        foreach ($finder as $file) {
            $found[$file->getRealPath()] = $file;
        }

        return $found;
    }


    /**
     * Get temp path
     *
     * @return string
     */
    public function getTemp()
    {
        return $this->temp;
    }


    /**
     * Set temp path
     *
     * @param string $temp
     * @return $this
     */
    public function setTemp($temp)
    {
        $this->temp = $this->createTemp($temp . DIRECTORY_SEPARATOR . 'latteCompiler');

        return $this;
    }


    /**
     * Get template
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }


    /**
     * Prepare files
     * skip exclude files
     *
     * @return array
     */
    public function prepareFiles()
    {
        if ($this->skippedFiles) {
            $out = $this->skippedFiles;
            $this->skippedFiles = [];

            return $out;
        }

        return $this->files;
    }


    /**
     * Run
     *
     * @throws RuntimeException
     */
    public function run()
    {
        error_reporting(E_ALL & ~(E_NOTICE));

        $latte = $this->template->getLatte();

        /* @var $file SplFileInfo */
        foreach ($this->prepareFiles() as $file) {
            try {
//                echo $file->getPathname() . "\n";

                $code = $latte->compile($file->getPathname());

                file_put_contents($this->getTempFilePath($file), $code);
            }
            catch (RuntimeException $e) {
                if (substr($e->getMessage(), 0, 30) !== 'Cannot include undefined block') {
                    throw $e;
                }
            }
            catch (CompileException $e) {
                $find = null;

                if (!preg_match('/Unknown macro \{(.*)\}/U', $e->getMessage(), $find)) {
                    throw $e;
                }

                $macroName = $find[1];

                $this->template->getLatte()->onCompile[] = function(Engine $engine) use ($macroName) {
                    $engine->addMacro($macroName, new EmptyMacro());
                };

                $this->skippedFiles[] = $file;
            }
        }

        if ($this->skippedFiles) {
            $this->run();
        }
    }


    /**
     * Create temp path
     *
     * @param string $path
     * @return string
     */
    private function createTemp($path)
    {
        if (is_dir($path)) {
            FileHelper::emptyDir($path, true);
        }

        FileHelper::createDir($path);

        return $path;
    }


    /**
     * Get temp file path name
     *
     * @param string $file
     * @return string
     */
    private function getTempFilePath($file)
    {
        return $this->getTemp() . DIRECTORY_SEPARATOR . uniqid() . '.php';
    }

}
