<?php /** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpIncludeInspection */

namespace Foris\Easy\Sdk\Tests\Installer;

use Foris\Easy\Console\Test\ConsoleTestSuite;
use Foris\Easy\Sdk\Installer\Application;

/**
 * Class TestCase
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use ConsoleTestSuite;

    /**
     * Application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Set up test environment
     */
    protected function setUp()
    {
        parent::setUp();
        $this->app = new Application();
    }

    /**
     * Gets application instance
     *
     * @return Application
     */
    protected function app()
    {
        return $this->app;
    }
}
