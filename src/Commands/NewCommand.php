<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Sdk\Installer\Commands;

use Foris\Easy\Console\Commands\Command;
use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use ZipArchive;

/**
 * Class NewCommand
 */
class NewCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sdk project.';

    /**
     * The console command help message.
     *
     * @var string
     */
    protected $help = '';

    /**
     * Skeleton project project base url.
     *
     * @var string
     */
    protected $baseUrl = 'https://github.com/itsanr-oris/easy-sdk';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the sdk project.'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    protected function handle()
    {
        $name = $this->argument('name');
        $directory = $name && $name !== '.' ? getcwd().'/'.$name : getcwd();

        $this->download($zipFile = $this->makeFilename())
            ->extract($zipFile, $directory)
            ->cleanUp($zipFile)
            ->initSdkProject();
    }

    /**
     * Generate a random temporary filename.
     *
     * @return string
     */
    protected function makeFilename()
    {
        return getcwd() . '/easy_sdk_' . md5(time() . uniqid()) . '.zip';
    }

    /**
     * Download the temporary Zip to the given file.
     *
     * @param        $zipFile
     * @param string $version
     * @return $this
     */
    protected function download($zipFile, $version = '')
    {
        if (empty($version)) {
            $version = 'master';
        }

        $response = (new Client())->get($this->baseUrl . '/archive/' . $version . '.zip');

        file_put_contents($zipFile, $response->getBody());

        return $this;
    }

    /**
     * Extract the Zip file into the given directory.
     *
     * @param $zipFile
     * @param $directory
     * @return NewCommand
     */
    protected function extract($zipFile, $directory)
    {
        $archive = new ZipArchive;

        $response = $archive->open($zipFile, ZipArchive::CHECKCONS);

        if ($response === ZipArchive::ER_NOZIP) {
            throw new RuntimeException("The zip file could not download. Verify that you are able to access: $this->baseUrl");
        }

        $archive->extractTo($directory);
        $archive->close();

        $this->fixDirectory($directory);
        return $this;
    }

    /**
     * Fix directory hierarchy errors.
     *
     * @param $directory
     */
    protected function fixDirectory($directory)
    {
        $directories = scandir($directory);

        if (empty($directories[2])
            || !is_dir($directory . '/' . $directories[2])
            || !file_exists($directory . '/' . $directories[2] . '/src')) {
            return ;
        }

        @rename($directory, $temp = $directory . '-temp');
        @rename($temp . '/' . $directories[2], $directory);
        @rmdir($temp);
    }

    /**
     * Clean-up the Zip file.
     *
     * @param  string  $zipFile
     * @return $this
     */
    protected function cleanUp($zipFile)
    {
        @chmod($zipFile, 0777);

        @unlink($zipFile);

        return $this;
    }

    /**
     * Initial setup of the SDK project configuration.
     *
     * @throws \Exception
     */
    protected function initSdkProject()
    {
        $this->getApplication()->call('init', ['directory' => $this->argument('name')], $this->getOutput());
    }
}
