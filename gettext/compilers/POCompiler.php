<?php

namespace Wame\LanguageModule\Gettext;

use Nette\Security\User;
use Gettext\Translations;
use Gettext\Merge;
use Gettext\Extractors\PhpCode;
use Wame\Utils\File\FileHelper;


class POCompiler
{
    /** @var User */
    private $user;

    /** @var array */
    private $options;

    /** @var string */
    private $path;

    /** @var string */
    private $temp;

    /** @var string */
    private $domain;

    /** @var array */
    private $languages = [];

    /** @var array */
    private $files = [];


    public function __construct(
        User $user
    ) {
        $this->user = $user;

        $this->options = PhpCode::$options;
    }


    /**
     * Gettext options
     *
     * @param string $section
     * @return array
     */
    public function getOptions($section = null)
    {
        if ($section) {
            return $this->options[$section];
        } else {
            return $this->options;
        }
    }


    /**
     * Extract comments
     *
     * false: to not extract comments
     * empty string: to extract all comments
     * non-empty string: to extract comments that start with that string
     *
     * @param mixed $comments
     * @return array
     */
    public function setExtractComments($comments)
    {
        $this->options['extractComments'] = $comments;

        return $this;
    }


    /**
     * Constants
     *
     * @param mixed $constants
     * @return array
     */
    public function setConstants($constants)
    {
        $this->options['constants'] = $constants;

        return $this;
    }


    /**
     * Add gettext function
     *
     * @param string $find
     * @param string $method e.g.: gettext, ngettext, pgettext, dpgettext...
     * @return $this
     */
    public function addFunction($find, $method)
    {
        $this->options['functions'][$find] = $method;

        return $this;
    }


    /**
     * Set path for find *.php files
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
     * Get path for find *.php files
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * Set temp path
     *
     * @param string $temp
     * @return $this
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;

        return $this;
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
     * Set domain
     *
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }


    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }


    /**
     * Set language list
     *
     * @param array $languages
     * @return $this
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }


    /**
     * Get language list
     *
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }


    /**
     * Run compile PO files
     */
    public function run()
    {
        $this->files = FileHelper::findFiles([$this->getPath(), $this->getTemp() . DIRECTORY_SEPARATOR . 'latteCompiler'], '*.php');

        foreach ($this->languages as $code => $locale) {
            $this->compile($code);
        }
    }


    /**
     * Compile PO file for lang
     *
     * @param string $lang
     */
    private function compile($lang)
    {
        $translations = $this->getPOFile($lang);

        foreach ($this->files as $file) {
            $phpTranslations = Translations::fromPhpCodeFile($file->getRealPath(), $this->getOptions());
            $translations->mergeWith($phpTranslations, Merge::ADD | Merge::HEADERS_ADD);
        }

        $dir = $this->getTemp() . $this->getLocalePath($lang);

        FileHelper::emptyDir($dir);
        FileHelper::createDir($dir);

        $translations->toPoFile($this->getPOFileName($dir));
    }


    /**
     * Find PO file or create new
     *
     * @param string $lang
     * @return Translations
     */
    private function getPOFile($lang)
    {
        $file = $this->getPOFileName($this->getPath() . $this->getLocalePath($lang));

        if (file_exists($file)) {
            return Translations::fromPoFile($file);
        } else {
            $translations = (new Translations())
                        ->setHeader('Project-Id-Version', $this->domain)
                        ->setHeader('X-Poedit-SourceCharset', 'UTF-8')
                        ->setHeader('X-Poedit-KeywordsList', implode(';', array_keys($this->getOptions('functions'))))
                        ->setHeader('X-Poedit-Basepath', $this->path)
                        ->setHeader('X-Poedit-SearchPath-0', '.')
                        ->setLanguage($lang)
                        ->setDomain($this->domain);

            if ($this->user->isLoggedIn()) {
                $translations->setHeader('Last-Translator', $this->user->getEntity()->getEmail());
            }

            return $translations;
        }
    }


    /**
     * Get locale path
     *
     * @param string $lang
     * @return string
     */
    private function getLocalePath($lang)
    {
        return DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . 'LC_MESSAGES';
    }


    /**
     * Get PO file name
     *
     * @param string $dir
     * @return string
     */
    private function getPOFileName($dir)
    {
        return $dir . DIRECTORY_SEPARATOR . $this->domain . '.po';
    }

}
