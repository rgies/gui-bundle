<?php
/**
 * Symfony2 - Rapid Development Bundle.
 *
 * @package     RGies\RadBundle\Executor
 * @author      Robert Gies <mail@rgies.com>
 * @copyright   Copyright © 2014 by Robert Gies
 * @license     MIT
 * @date        2014-02-04
 */

namespace RGies\GuiBundle\Executor;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Bundle\FrameworkBundle\Console\Application;

use RGies\GuiBundle\Output\StringOutput;
use RGies\GuiBundle\Formatter\HtmlConsoleOutputFormatter;

/**
 * Class CommandExecutor
 * @package RGies\GuiBundle\Executor
 */
class CommandExecutor
{
    protected $baseKernel;

    public function __construct(Kernel $baseKernel)
    {
        $this->baseKernel = $baseKernel;
    }

    public function execute($commandString)
    {
        $input = new StringInput($commandString);
        $input->setInteractive(false);

        $output = new StringOutput();
        $formatter = $output->getFormatter();
        $formatter->setDecorated(true);
        $output->setFormatter(new HtmlConsoleOutputFormatter($formatter));

        //$application = new Application($this->getContainer()->get('kernel'));
        $application = $this->getApplication($input);
        $kernel = $application->getKernel();
        chdir($kernel->getRootDir() . '/..');

        $application->setAutoExit(false);

        ob_start();
        $errorCode = $application->run($input, $output);
        $result = $output->getBuffer() || ob_get_contents();
        ob_end_clean();

        return array(
            'input'       => $commandString,
            'output'      => $output->getBuffer(),
            'environment' => $kernel->getEnvironment(),
            'error_code'  => $errorCode
        );
    }

    protected function getApplication($input = null)
    {
        $kernel = $this->getKernel($input);

        return new Application($kernel);
    }

    protected function getKernel($input = null)
    {
        if($input === null) {
            return $this->baseKernel;
        }

        $env = $input->getParameterOption(array('--env', '-e'), $this->baseKernel->getEnvironment());
        $debug = !$input->hasParameterOption(array('--no-debug', ''));

        if($this->baseKernel->getEnvironment() === $env && $this->baseKernel->isDebug() === $debug) {
            return $this->baseKernel;
        }

        $kernelClass = new \ReflectionClass($this->baseKernel);

        return $kernelClass->newInstance($env, $debug);
    }

}
