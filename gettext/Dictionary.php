<?php

namespace Wame\LanguageModule\Gettext;

use SplFileInfo;
use Nette\DI\Container;
use Nette\Utils\Finder;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\NotSupportedException;
use Gettext\Translations;
use Gettext\Merge;
use h4kuna\Gettext\GettextException;
use Wame\PluginLoader;
use Wame\Utils\File\FileHelper;
use Wame\LanguageModule\Gettext\Download;


class Dictionary extends \h4kuna\Gettext\Dictionary
{
    const DOMAIN = 'Core';
    const DOMAIN_PATH = 'domainPath';


    /** @var Container */
    private $container;

    /** @var PluginLoader */
    private $pluginLoader;

    /**  @var array */
    private $domains = [];

    /** @var array */
    private $domainPath = [];

    /** @var string */
    private $domain;

    /** @var Cache */
    private $cache;

    /** @var string */
    private $customTemplate;

    /** @var array */
    private $alternativePaths = [];


    /**
     * Check path wiht dictionary
     *
     * @param string $path
     * @throws GettextException
     */
    public function __construct(
        Container $container,
        IStorage $storage
    ) {
        $this->container = $container;
        $this->pluginLoader = $container->getService('plugin.loader');
        $this->cache = new Cache($storage, __CLASS__);
        $this->loadDomains();
    }


    /**
     * What domain you want
     *
     * @param string $domain
     * @return self
     * @throws GettextException
     */
    public function setDomain($domain)
    {
        if (!is_string($domain)) {
            $domain = $this->getModule($domain);
        }

        if (!$domain || $this->domain == $domain || !isset($this->domains[$domain])) {
            return $this;
        }

        $this->loadDomain($domain);
        $this->domain = textdomain($domain);

        return $this;
    }


    /**
     * Load dictionary if not loaded.
     *
     * @param string $domain
     * @throws GettextException
     */
    public function loadDomain($domain)
    {
        if (!isset($this->domains[$domain])) {
            throw new GettextException('This domain does not exists: ' . $domain);
        }

        if ($this->domains[$domain] === FALSE) {
            bindtextdomain($domain, $this->domainPath[$domain]);
            bind_textdomain_codeset($domain, 'UTF-8');
            $this->domains[$domain] = TRUE;
        }

        return $domain;
    }


    /** @return string */
    public function getDomain()
    {
        return $this->domain;
    }


    /** @return array */
    public function getDomains()
    {
        return $this->domains;
    }


    /**
     * Load all dictionaries.
     *
     * @param string $default
     */
    public function loadAllDomains($default)
    {
        foreach ($this->domains as $domain => $_n) {
            $this->loadDomain($domain);
        }

        $this->setDomain($default);
    }


    /**
     * Language files download
     *
     * @param string $lang
     */
    public function download($lang)
    {
        Download::lang($lang);
    }


    /**
     * Save uploaded files
     *
     * @param string $lang
     * @param FileUpload $po
     * @param FileUpload $mo
     */
    public function upload($lang, FileUpload $po, FileUpload $mo)
    {
        throw new NotSupportedException("This method is not supported");
    }


    /**
     * Filesystem path for domain
     *
     * @param string $lang
     * @param string $extension
     * @return string
     */
    public function getFile($lang, $extension = 'mo')
    {
        throw new NotSupportedException("This method is not supported");
    }


    /**
     * Check for available domain.
     *
     * @return array
     */
    public function loadDomains()
    {
        if ($this->cache->load(self::DOMAIN) !== NULL) {
            $this->domainPath = $this->cache->load(self::DOMAIN_PATH);

            return $this->domains = $this->cache->load(self::DOMAIN);
        }

        $this->customTemplate = $this->getCustomTemplate();
        $this->alternativePaths = $this->getAlternativePaths();

        $files = $match = $domains = $domainPath = [];
        $paths = $this->loadLocalePaths();

        foreach ($paths as $path) {
            foreach (Finder::findFiles('*.po')->from($path) as $file) {
                /* @var $file SplFileInfo */
                if (preg_match('/' . preg_quote($path, '/') . '(.*)(?:\\\|\/)/U', $file->getPath(), $match)) {
                    $_dictionary = $file->getBasename('.po');
                    $domains[$match[1]][$_dictionary] = $_dictionary;
                    $files[] = $file->getPathname();
                    $domainPath[$_dictionary] = $this->findPath($_dictionary, $file);
                }
            }
        }

        if (count($domains) == 0) {
            throw new GettextException('*.po files not found, run php index.php generate:po');
        }

        $dictionary = $domains;

        foreach ($domains as $lang => $_domains) {
            unset($dictionary[$lang]);

            foreach ($dictionary as $value) {
                $diff = array_diff($_domains, $value);

                if ($diff) {
                    throw new GettextException('For this language (' . $lang . ') you have one or more different dicitonaries: ' . implode('.mo, ', $diff) . '.mo');
                }
            }
        }

        $data = array_combine($_domains, array_fill_keys($_domains, FALSE));
        $this->domains = $this->cache->save(self::DOMAIN, $data, array(Cache::FILES => $files));
        $this->domainPath = $this->cache->save(self::DOMAIN_PATH, $domainPath, array(Cache::FILES => $files));

        return $this->domains;
    }


    /**
     * Find locale path
     *
     * @param string $domain
     * @param SplFileInfo $file
     * @return string
     */
    private function findPath($domain, $file)
    {
        $alternativePaths = $this->alternativePaths;
        $translations = null;
        $explode = explode('locale', $file->getRealPath());
        $fileName = $explode[1];

    // App
        if (isset($alternativePaths['app'][$domain])) {
            $path = $alternativePaths['app'][$domain];
            $translations = Translations::fromPoFile($path . $fileName);
        }

    // Template
        if (isset($alternativePaths['template'][$this->customTemplate][$domain])) {
            $templatePath = $alternativePaths['template'][$this->customTemplate][$domain];

            if ($translations) {
                $templateTranslations = Translations::fromPoFile($templatePath . $fileName);
                $translations->mergeWith($templateTranslations, Merge::ADD | Merge::HEADERS_ADD);
            } else {
                $path = $templatePath;
                $translations = Translations::fromPoFile($templatePath);
            }
        }

    // Vendor
        if ($translations) {
            $vendorTranslations = Translations::fromPoFile($file->getRealPath());
            $translations->mergeWith($vendorTranslations, Merge::ADD | Merge::HEADERS_ADD);
        } else {
            $path = FileHelper::dirnameWithLevels($file->getPath(), 2);
        }

        return $path;
    }


    /**
     * @return string[] Locale paths
     */
    private function loadLocalePaths()
    {
        $paths = [];
        $paths[] = APP_PATH . DIRECTORY_SEPARATOR . 'locale';

        $paths[] = VENDOR_PATH . "/wame/LanguageModule/locale";
        
//        foreach ($this->pluginLoader->getPlugins() as $plugin) {
//            $paths[] = $plugin->getPluginPath();
//        }

        foreach ($paths as $index => $path) {
            if (!file_exists($path)) {
                unset($paths[$index]);
            }
        }

        return $paths;
    }


    /**
     * Get alternative paths App, Templates
     *
     * @return array
     */
    private function getAlternativePaths()
    {
        $paths = [];

        foreach (Finder::findFiles('*.po')->from(APP_PATH) as $file) {
            $_dictionary = $file->getBasename('.po');

            $paths['app'][$_dictionary] = FileHelper::dirnameWithLevels($file->getPath(), 2);
        }

        foreach (Finder::findFiles('*.po')->from(TEMPLATES_PATH . DIRECTORY_SEPARATOR . $this->customTemplate) as $file) {
            $_dictionary = $file->getBasename('.po');

            $paths['template'][$this->customTemplate][$_dictionary] = FileHelper::dirnameWithLevels($file->getPath(), 2);
        }

        return $paths;
    }


    /**
     * Get module name from namespace or fileDir
     *
     * @param mixed $namespace
     * @return string
     */
    public static function getModule($namespace)
    {
        if ($namespace instanceof \App\Core\Presenters\BasePresenter || $namespace instanceof \Wame\Core\Components\BaseControl) {
            $reflection = new \Nette\Reflection\ClassType($namespace);
            $namespace = $reflection->getFileName();
        }

        if (is_object($namespace)) {
            $class = new \Nette\Reflection\ClassType($namespace);
            $namespace = $class->getNamespaceName();
        }

        if (file_exists($namespace)) {
            preg_match_all("/" . strtolower(PACKAGIST_NAME) . "\/(\w*)/", $namespace, $match);
        } else {
            preg_match_all("/" . ucfirst(strtolower(PACKAGIST_NAME)) . ".(\w*)/", $namespace, $match);
        }

        if (count($match) > 0) {
            return implode('.', $match[1]);
        } else {
            return null;
        }
    }


    /**
     * Get custom template name
     *
     * @return string
     */
    private function getCustomTemplate()
    {
        if (isset($this->container->getParameters()['customTemplate'])) {
            return $this->container->getParameters()['customTemplate'];
        } else {
            return null;
        }
    }

}
