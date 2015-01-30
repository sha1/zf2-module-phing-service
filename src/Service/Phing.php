<?php

namespace BsbPhingService\Service;

use BsbPhingService\Options\Phing as PhingOptions;
use BsbPhingService\Options\Service as ServiceOptions;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class Phing
{

    /**
     * @var ServiceOptions
     */
    protected $options;

    /**
     * @var PhingOptions
     */
    protected $phingOptions;

    public function __construct(ServiceOptions $options = null, PhingOptions $phingOptions = null)
    {
        $this->options      = $options;
        $this->phingOptions = $phingOptions;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(ServiceOptions $options)
    {
        $this->options = $options;
    }

    public function getPhingOptions()
    {
        return $this->phingOptions;
    }

    public function setPhingOptions(PhingOptions $phingOptions)
    {
        $this->phingOptions = $phingOptions;
    }

    /**
     *
     * @param  type                   $target
     * @param  null|array|Traversable $options
     * @return type
     */
    public function build($target = null, $options = null)
    {
        $phingOptions = clone $this->phingOptions;

        if ($options) {
            if (!is_array($options) && !$options instanceof Traversable) {
                throw new \InvalidArgumentException(sprintf(
                    'Parameter provided to %s must be an array or Traversable',
                    __METHOD__
                ));
            }

            $phingOptions->setFromArray($options);
        }

        return $this->doBuild($target, $phingOptions);
    }

    protected function doBuild($target, PhingOptions $options)
    {
        if (!self::hasExec()) {
            throw new \RuntimeException("Not able to use PHP's exec method");
        }

        $builder = new ProcessBuilder();

        $builder->setPrefix($this->options->getPhingBin());
        $builder->setArguments($this->getPhingCommandArgumentsArray($options));

        foreach($this->getEnv() as $key=>$value) {
            $builder->setEnv($key, $value);
        }

        $builder->add($target);

        $process = $builder->getProcess();

        $process->run();

        $result = array(
            'command'      => $process->getCommandLine(),
            'output'       => $process->getOutput(),
            'returnStatus' => $process->getExitCode()
        );

        return $result;
    }

    public static function hasExec()
    {
        static $capable;

        if ($capable === null) {
            $_capable = function_exists('exec');

            foreach (array_map('trim', explode(',', ini_get('disable_functions'))) as $func) {
                if ($func == 'exec') {
                    $_capable = false;
                }
            }

            $capable = $_capable;
        }

        return $capable;
    }

    /**
     * Construct an array with commands to configure the cli environment
     *
     * @return array
     */
    protected function getEnv()
    {
        $env = array();

        $env['PHP_COMMAND']   = $this->options->getPhpBin();
        $env['PHING_HOME']    = $this->options->getPhingPath();
        $env['PHP_CLASSPATH'] = sprintf('%s\classes', $this->options->getPhingPath());

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $env['PATH']          = sprintf('%s;%s\bin', $_SERVER['PATH'], $this->options->getPhingPath());
        } else {
            $env['PATH']          = sprintf('%s:%s\bin', $_SERVER['PATH'], $this->options->getPhingPath());
        }

        return $env;
    }

    /**
     * Construct an array with arguments to configure the phing binary
     *
     * @param  PhingOptions $options
     * @return array
     */
    protected function getPhingCommandArgumentsArray(PhingOptions $options)
    {
        $arguments = array();

        if ($options->getBuildFile()) {
            $arguments[] = "-buildfile";
            $arguments[] = $options->getBuildFile();
        }

        if ($options->getLogger()) {
            $arguments[] = "-logger";
            $arguments[] = $options->getLogger();
        }

        if ($options->getLogFile()) {
            $arguments[] = "-logfile";
            $arguments[] = $options->getLogFile();
        }

        if ($options->getPropertyFile()) {
            $arguments[] = "-propertyfile";
            $arguments[] = $options->getPropertyFile();
        }

        if ($options->getInputHandler()) {
            $arguments[] = "-inputhandler";
            $arguments[] = $options->getInputHandler();
        }

        if ($options->getFind()) {
            $arguments[] = "-find";
            $arguments[] = $options->getFind();
        }

        if ($options->getLongTargets()) {
            $arguments[] = "-longtargets";
        }

        if ($options->getList()) {
            $arguments[] = "-list";
        }

        foreach ($options->getProperties() as $key => $value) {
            $arguments[] = sprintf("-D%s=%s", (string) $key, (string) escapeshellarg($value));
        }

        return $arguments;
    }
}