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

    /** @var array */
    private $templates;

    /** @var OutputInterface */
    private $output;


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
        $this->templates = FileHelper::findFolders(TEMPLATES_PATH);
    }


    /**
     * Compile PO files all modules
     */
    public function run($output = null)
    {
        $this->output = $output;

        $plugins = $this->pluginLoader->getPlugins();

        $count = count($plugins);
        $i = 1;

        foreach ($plugins as $plugin) {
            $this->writeOutput(sprintf('COMPILE <info>%s</info> (%s/%s)', $plugin->getName(), $i++, $count));

            $path = $plugin->getPluginPath();
            $temp = $path . DIRECTORY_SEPARATOR . 'temp';
            $explode = explode(DIRECTORY_SEPARATOR, $path);
            $domain = end($explode);

        // Vendor
            $this->generate($path, $temp, $domain);

        // App
            $this->generate(APP_PATH . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR, $temp, $domain);

        // Templates
            foreach ($this->templates as $name => $folder) {
                $this->generate(TEMPLATES_PATH . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $domain . DIRECTORY_SEPARATOR, $temp, $domain);
            }
        }
    }


    /**
     * Generate *.po, *.mo files
     *
     * @param string $path
     * @param string $temp
     * @param string $domain
     */
    private function generate($path, $temp, $domain)
    {
        if (file_exists($path)) {
            $vendorPath = $path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . PACKAGIST_NAME;

        //  Ak chceme vyprazdnit prekladove subory tak vytvorime úplne nové súbory
//            if ($domain != 'Core') { FileHelper::emptyDir($path . DIRECTORY_SEPARATOR . 'locale'); }

            $this->createTemp($temp);
            $this->createGitIgnore($temp);

            $this->compileLatte($path, $temp, $vendorPath);
            $this->compilePO($path, $temp, $domain);
            $this->checkPO($temp, $domain);
            $this->compileMO($temp);

            $this->overwriteFiles($temp, $path);
            $this->emptyTemp($temp);

            $this->vendorPathProcess($vendorPath, $temp, $domain);
        }
    }


    /**
     * Generate *.po, *.mo files in vendor path
     *
     * @param string $dir
     * @param string $temp
     * @param string $prefix
     */
    private function vendorPathProcess($dir, $temp, $prefix = null)
    {
        if (file_exists($dir)) {
            $plugins = FileHelper::findFolders($dir . DIRECTORY_SEPARATOR);

            foreach ($plugins as $name => $folder) {
                $path = $dir . DIRECTORY_SEPARATOR . $name;

                $domain = '';
                if ($prefix) { $domain .= $prefix . '.'; }
                $domain .= $name;

            //  Ak chceme vyprazdnit prekladove subory tak vytvorime úplne nové súbory
//                FileHelper::emptyDir($path . DIRECTORY_SEPARATOR . 'locale', true);

                $this->compileLatte($path, $temp);
                $this->compilePO($path, $temp, $domain);
                $this->checkPO($temp, $domain);
                $this->compileMO($temp);

                $this->overwriteFiles($temp, $path);
                $this->emptyTemp($temp);
            }
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
     * Check count PO translations
     * if null remove file
     *
     * @param string $path
     * @param string $domain
     */
    private function checkPO($path, $domain)
    {
        if ($domain == 'Core') return;

        foreach ($this->languages as $code => $locale) {
            $dir = $path . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR;

            $translations = Translations::fromPoFile($dir . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $domain . '.po');

            if (count($translations) == 0) {
                FileHelper::emptyDir($dir, true);
            }
        }
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

}
