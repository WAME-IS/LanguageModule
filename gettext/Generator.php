<?php

namespace Wame\LanguageModule\Gettext;

use Nette\DI\Container;
use Symfony\Component\Console\Output\OutputInterface;
use Gettext\Translations;
use Wame\PluginLoader;
use Wame\Utils\File\FileHelper;
use Wame\LanguageModule\Gettext\LatteCompiler;
use Wame\LanguageModule\Gettext\POCompiler;
use Wame\LanguageModule\Gettext\MOCompiler;
use Wame\LanguageModule\Repositories\LanguageRepository;


class Generator
{
    const ADMIN_MODULE = 'AdminModule';


    /** @var PluginLoader */
    private $pluginLoader;

    /** @var LatteCompiler */
    private $latteCompiler;

    /** @var POCompiler */
    private $POCompiler;

    /** @var MOCompiler */
    private $MOCompiler;

    /** @var array */
    private $languages;

    /** @var OutputInterface */
    private $output;

    /** @var array */
    private $timer = [];


    public function __construct(
        Container $container,
        LatteCompiler $latteCompiler,
        POCompiler $POCompiler,
        MOCompiler $MOCompiler,
        LanguageRepository $languageRepository
    ) {
        $this->pluginLoader = $container->getService('plugin.loader');
        $this->latteCompiler = $latteCompiler;
        $this->POCompiler = $POCompiler;
        $this->MOCompiler = $MOCompiler;
        $this->languages = $languageRepository->findPairs([], 'locale', ['code' => 'ASC'], 'code');
    }


    /**
     * Compile PO files all modules
     */
    public function run($output = null)
    {
        $this->output = $output;

        $plugins = $this->pluginLoader->getPlugins();

        $this->pluginsProcess($plugins);
        $this->adminProcess($plugins);
    }


    /**
     * Compile plugins
     *
     * @param array $plugins
     */
    public function pluginsProcess($plugins)
    {
        $count = count($plugins);
        $i = 1;

        foreach ($plugins as $plugin) {
            $path = $plugin->getPluginPath();
            $temp = $path . DIRECTORY_SEPARATOR . 'temp';
            $explode = explode(DIRECTORY_SEPARATOR, $path);
            $domain = end($explode);

            FileHelper::emptyDir($path . DIRECTORY_SEPARATOR . 'locale');

            $this->timer = [];
            $this->writeOutput(sprintf('COMPILE <info>%s</info> (%s/%s)', $plugin->getName(), $i, $count));
            $i++;

            $this->createTemp($temp);
            $this->createGitIgnore($temp);

            $this->compileLatte($path, $temp, $path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . PACKAGIST_NAME . DIRECTORY_SEPARATOR . self::ADMIN_MODULE);
            $this->compilePO($path, $temp, $domain);
            $this->compileMO($temp);

            $this->overwriteFiles($temp, $path);
            $this->emptyTemp($temp);
        }
    }


    /**
     * Compile adminModule
     *
     * @param array $plugins
     */
    public function adminProcess($plugins)
    {
        $this->writeOutput('<info>START</info> AdminProcess');

        $adminPlugins = [];
        $count = count($plugins);
        $i = 1;

        foreach ($plugins as $plugin) {
            $path = $plugin->getPluginPath();
            $adminPath = $path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . PACKAGIST_NAME . DIRECTORY_SEPARATOR . self::ADMIN_MODULE;

            if (is_dir($adminPath)) {
                $temp = $path . DIRECTORY_SEPARATOR . 'temp';
                $explode = explode(DIRECTORY_SEPARATOR, $path);
                $domain = end($explode);

                $this->timer = [];
                $this->writeOutput(sprintf('COMPILE ADMIN <info>%s</info> (%s/%s)', $plugin->getName(), $i, $count));

                $this->compileLatte($adminPath, $temp);
                $this->compilePO($adminPath, $temp, $domain);
                $this->compilePO($temp . DIRECTORY_SEPARATOR . 'latteCompiler', $temp . DIRECTORY_SEPARATOR . 'latteCompiler', $domain);

                $adminPlugins[$domain] = $temp . DIRECTORY_SEPARATOR . 'locale';
            } else {
                $this->writeOutput(sprintf('SKIP ADMIN <info>%s</info> (%s/%s)', $plugin->getName(), $i, $count));
            }

            $i++;
        }

        $this->compileAdmin($adminPlugins);
        $this->emptyAdminTemp($adminPlugins);
    }


    /**
     * ADD Translations to AdminModule
     *
     * @param array $adminPlugins
     */
    private function compileAdmin($adminPlugins)
    {
        $this->writeOutput('ADD Translations to AdminModule');
        $adminPath = VENDOR_PATH . DIRECTORY_SEPARATOR . PACKAGIST_NAME . DIRECTORY_SEPARATOR . self::ADMIN_MODULE . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;

        foreach ($this->languages as $code => $locale) {
            $dir = $adminPath . $code . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR;
            $translations = Translations::fromPoFile($dir . self::ADMIN_MODULE . '.po');

            foreach ($adminPlugins as $name => $path) {
                $translations->addFromPoFile($path . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $name . '.po');
                $translations->addFromPoFile($path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'latteCompiler' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $name . '.po');
            }

            $translations->setHeader('Project-Id-Version', self::ADMIN_MODULE)
                            ->setHeader('X-Poedit-Basepath', $adminPath . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR)
                            ->toPoFile($dir . self::ADMIN_MODULE . '.po');

            $this->compileMO($dir);
        }
    }


    private function emptyAdminTemp($adminPlugins)
    {
        foreach ($adminPlugins as $path) {
            $this->emptyTemp($path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
        }
    }


    /**
     * Find all *.latte files in module
     * and compile to *.php file
     *
     * @param string $path
     * @param string $temp
     * @param string $exclude
     * @return boolean
     */
    private function compileLatte($path, $temp, $exclude = null)
    {
        $latteCompiler = clone $this->latteCompiler;
        $latteCompiler->addInclude($path);
        $latteCompiler->setTemp($temp);

        if (is_dir($exclude)) {
            $latteCompiler->addExclude($exclude);
        }

        return $latteCompiler->run();
    }


    /**
     * Compile PO files
     * find new phrase and append to PO file
     *
     * @param string $path
     * @param string $temp
     * @param string $domain
     */
    private function compilePO($path, $temp, $domain)
    {
        $POCompiler = $this->POCompiler;
        $POCompiler->addFunction('_', 'gettext');
        $POCompiler->setPath($path);
        $POCompiler->setTemp($temp);
        $POCompiler->setDomain($domain);
        $POCompiler->setLanguages($this->languages);
        $POCompiler->run();
    }


    /**
     * Convert PO to MO
     *
     * @param string $path
     */
    private function compileMO($path)
    {
        $MOCompiler = $this->MOCompiler;
        $MOCompiler->setPath($path);
        $MOCompiler->run();
    }


    /**
     * Overwrite files
     *
     * @param string $from
     * @param string $to
     */
    private function overwriteFiles($from, $to)
    {
        $pathFrom = $from . DIRECTORY_SEPARATOR . 'locale';
        $pathTo = $to . DIRECTORY_SEPARATOR . 'locale';

        FileHelper::moveDir($pathFrom, $pathTo);
    }


    /**
     * Empty temp
     *
     * @param string $temp
     */
    private function emptyTemp($temp)
    {
        FileHelper::emptyDir([
            $temp . DIRECTORY_SEPARATOR . 'latteCompiler',
            $temp . DIRECTORY_SEPARATOR . 'locale'
        ], true);
    }


    /**
     * Create temp folder
     *
     * @param string $path
     * @return string
     */
    private function createTemp($path)
    {
        return FileHelper::createDir($path);
    }


    /**
     * Create GIT ignore file
     * if not exists
     *
     * @param string $temp
     */
    private function createGitIgnore($temp)
    {
        $fileName = $temp . DIRECTORY_SEPARATOR . '.gitignore';

        if (!file_exists($fileName)) {
            $content = "*\n!.gitignore";

            $fp = fopen($fileName, 'wb');

            fwrite($fp, $content);
            fclose($fp);
        }
    }


    /**
     * Write console output
     *
     * @param string $text
     */
    private function writeOutput($text)
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->writeLn($text);
        }
    }


    /**
     * Add time to timer
     *
     * @param string $title
     * @param numeric $time
     * @return $this
     */
    private function addTime($title, $time)
    {
        $this->timer[] = $title . ': ' . $time * 1000;

        return $this;
    }

}
