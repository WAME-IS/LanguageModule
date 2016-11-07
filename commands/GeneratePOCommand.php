<?php

namespace Wame\LanguageModule\Commands;

use Nette\Mail\SmtpException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wame\LanguageModule\Gettext\Generator;


class GeneratePOCommand extends Command
{
    /** @var Generator */
    private $generator;


    public function injectServices(
        Generator $generator
    ) {
        $this->generator = $generator;
    }


    protected function configure()
    {
        $this->setName('generate:po')
                ->setDescription('Generate PO language files');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $start = time();
            $output->writeLn(sprintf('<info>START</info> %s', date('H:i:s', $start)));

            $this->generator->run($output);

            $end = time();
            $output->writeLn(sprintf('<info>END</info> %s total %s', date('H:i:s', $end), gmdate('H:i:s', $end - $start)));

            return 0; // zero return code means everything is ok
        }
        catch (SmtpException $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');

            return 1; // non-zero return code means error
        }
    }

}
