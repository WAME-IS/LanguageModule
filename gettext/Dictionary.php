<?php

namespace Wame\LanguageModule\Gettext;

use h4kuna\Gettext\GettextException;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\Http\FileUpload;
use Nette\NotSupportedException;
use Nette\Utils\Finder;
use SplFileInfo;
use Wame\PluginLoader;

class Dictionary extends \h4kuna\Gettext\Dictionary
{
    const DOMAIN = 'Core';
    const DOMAIN_PATCH = 'domainPath';

    /** @var PluginLoader */
    private $pluginLoader;

    /**  @var array */
    private $domains = [];

    /** @var array */
    private $domainPatch = [];

    /** @var string */
    private $domain;

    /** @var Cache */
    private $cache;

    /**
     * Check path wiht dictionary
     *
     * @param string $path
     * @throws GettextException
     */
    public function __construct(Container $container, IStorage $storage)
    {
        $this->pluginLoader = $container->getService('plugin.loader');
        $this->cache = new Cache($storage, __CLASS__);
        $this->loadDomains();
    }


    /**
     * What domain you want.
     *
     * @param string $domain
     * @return self
     * @throws GettextException
     */
    public function setDomain($domain)
    {
        if ($this->domain == $domain) {
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
            bindtextdomain($domain, $this->domainPatch[$domain]);
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
     * Offer file download.
     *
     * @param string $language
     * @throws GettextException
     */
    public function download($language)
    {
        dump($this->loadDomains());
        exit;

//        throw new NotSupportedException("This method is not supported");
    }


    /**
     * Save uploaded files.
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
//        if ($this->cache->load(self::DOMAIN) !== NULL) {
//            $this->domainPatch = $this->cache->load(self::DOMAIN_PATCH);
//
//            return $this->domains = $this->cache->load(self::DOMAIN);
//        }

        $files = $match = $domains = $domainPath = [];
        $paths = $this->loadLocalePaths();
        foreach ($paths as $path) {
            foreach (Finder::findFiles('*.po')->from($path) as $file) {
                /* @var $file SplFileInfo */
                if (preg_match('/' . preg_quote($path, '/') . '(.*)(?:\\\|\/)/U', $file->getPath(), $match)) {
                    $_dictionary = $file->getBasename('.po');
                    $domains[$match[1]][$_dictionary] = $_dictionary;
                    $files[] = $file->getPathname();
                    $domainPath[$_dictionary] = $match[0];
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
        $this->domainPatch = $this->cache->save(self::DOMAIN_PATCH, $domainPath, array(Cache::FILES => $files));

        return $this->domains;
    }


    /**
     * @return string[] Locale paths
     */
    private function loadLocalePaths()
    {
        $paths = [];
        $paths[] = APP_PATH . DIRECTORY_SEPARATOR . 'locale';

        foreach ($this->pluginLoader->getPlugins() as $plugin) {
            $paths[] = $plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'locale';
        }

        foreach ($paths as $index => $path) {
            if (!file_exists($path)) {
                unset($paths[$index]);
            }
        }

        return $paths;
    }

}
