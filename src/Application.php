<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Sdk\Installer;

/**
 * Class Application
 */
class Application extends \Foris\Easy\Console\Application
{
    /**
     * The easy-sdk installer version.
     *
     * @var string
     */
    const VERSION = '2.0.0';

    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct(null, 'Easy sdk Installer', self::VERSION);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
