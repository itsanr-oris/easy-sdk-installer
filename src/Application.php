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
    const VERSION = '1.0.0';

    /**
     * Application constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(array_merge($options, [
            'name' => 'Easy sdk Installer',
            'version' => self::VERSION,
        ]));
    }

    /**
     * Register the commands for the application.
     *
     * @throws \ReflectionException
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
