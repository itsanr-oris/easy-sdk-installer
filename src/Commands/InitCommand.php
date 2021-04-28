<?php /** @noinspection PhpParamsInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Sdk\Installer\Commands;

use Foris\Easy\Console\Commands\Command;
use Foris\Easy\Support\Filesystem;
use Foris\Easy\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Class InitCommand
 */
class InitCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initial setup of the SDK project configuration.';

    /**
     * The console command help message.
     *
     * @var string
     */
    protected $help = 'Help message';

    /**
     * The composer.json file content.
     *
     * @var array
     */
    protected $composer = [];

    /**
     * Git configuration.
     *
     * @var array
     */
    protected $gitConfig = [];

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['directory', InputArgument::OPTIONAL, 'The root directory of the sdk project.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['name', null, InputOption::VALUE_OPTIONAL, 'Name of the package'],
            ['description', null, InputOption::VALUE_OPTIONAL, 'Description of package'],
            ['author', null, InputOption::VALUE_OPTIONAL, 'Author name of package'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Root namespace of package']
        ]);
    }

    /**
     * Execute the console command.
     */
    protected function handle()
    {
        $this->line('');
        $this->line('This command will guide you through the initial setup of the SDK project configuration.');

        $name = $this->getPackageName();
        $description = $this->getPackageDescription();
        $author = $this->getPackageAuthor();
        $namespace = $this->getPackageRootNamespace($name);

        // 替换掉项目内的字符串
        $composer = $this->getComposer();

        $this->replacePackageName($composer, $name)
            ->replacePackageDescription($composer, $description)
            ->replacePackageAuthor($composer, $author)
            ->replacePackageNamespace($composer, $namespace)
            ->writeComposer($composer)
            ->composerInstall();
    }

    /**
     * Gets the composer.json file content.
     *
     * @return array|mixed
     */
    protected function getComposer()
    {
        if (!empty($this->composer)) {
            return $this->composer;
        }

        $path = getcwd() . '/' . $this->getDirectoryInput() . '/composer.json';

        if (!file_exists($path)) {
            throw new \RuntimeException('');
        }

        $composer = json_decode(file_get_contents($path), true);
        return $this->composer = is_array($composer) ? $composer : [];
    }

    /**
     * Write the composer.json file content.
     *
     * @param $composer
     * @return $this
     */
    protected function writeComposer($composer)
    {
        $path = getcwd() . '/' . $this->getDirectoryInput() . '/composer.json';
        file_put_contents($path, str_replace('\/', '/', json_encode($composer,JSON_PRETTY_PRINT)));
        return $this;
    }

    /**
     * Gets the git configuration.
     *
     * @return array
     */
    protected function getGitConfig()
    {
        if (!empty($this->gitConfig)) {
            return $this->gitConfig;
        }

        $finder = new ExecutableFinder();
        $gitBin = $finder->find('git');
        $cmd = new Process(array($gitBin, 'config', '-l'));
        $cmd->run();

        $this->gitConfig = [];
        if ($cmd->isSuccessful()) {
            preg_match_all('{^([^=]+)=(.*)$}m', $cmd->getOutput(), $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $this->gitConfig[$match[1]] = $match[2];
            }
        }

        return $this->gitConfig;
    }

    /**
     * Gets the sdk project root directory from the input.
     *
     * @return array|null|string
     */
    protected function getDirectoryInput()
    {
        $directory = $this->argument('directory');
        return empty($directory) || $directory == '.' ? '' : $directory;
    }

    /**
     * Gets the package base name from input
     *
     * @return string
     */
    protected function getPackageBaseName()
    {
        $name = basename(getcwd() . '/' . $this->getDirectoryInput());
        $name = preg_replace('{(?:([a-z])([A-Z])|([A-Z])([A-Z][a-z]))}', '\\1\\3-\\2\\4', $name);
        return strtolower($name);
    }

    /**
     * Gets the package vendor name.
     *
     * @param string $default
     * @return mixed|string
     */
    protected function getVendorName($default = '')
    {
        $git = $this->getGitConfig();

        if (!empty($_SERVER['COMPOSER_DEFAULT_VENDOR'])) {
            return $_SERVER['COMPOSER_DEFAULT_VENDOR'];
        }

        if (isset($git['github.user'])) {
            return $git['github.user'];
        }

        if (!empty($_SERVER['USERNAME'])) {
            return $_SERVER['USERNAME'];
        }

        if (!empty($_SERVER['USER'])) {
            return $_SERVER['USER'];
        }

        return get_current_user() ? get_current_user() : $default;
    }

    /**
     * Gets the package name.
     *
     * @return array|bool|null|string|string[]
     */
    protected function getPackageName()
    {
        $name = $this->option('name');

        if (empty($name)) {
            $name = $this->getPackageBaseName();
            $name = strtolower($this->getVendorName($name) . '/' . $name);
        }

        $name = $this->ask('Package name (<vendor>/<name>)', $name);

        // vendor/name
        if (!preg_match('{^[a-z0-9_.-]+/[a-z0-9_.-]+$}D', $name)) {
            throw new \InvalidArgumentException(
                'The package name '.$name.' is invalid, it should be lowercase and have a vendor name, a forward slash, and a package name, matching: [a-z0-9_.-]+/[a-z0-9_.-]+'
            );
        }

        return $name;
    }

    /**
     * Gets the package description.
     *
     * @return mixed
     */
    protected function getPackageDescription()
    {
        $description = $this->option('description');

        if (empty($description)) {
            $description = "This is an easy-sdk for " . $this->getPackageBaseName() . ".";
        }

        return $this->ask("Description", $description);
    }

    /**
     * Gets the package author info.
     *
     * @return array
     */
    protected function getPackageAuthor()
    {
        $author = $this->option('author');

        if (empty($author)) {
            $author = $this->getAuthorString();
            $author = empty($author) ? 'developer <developer@easy-sdk.com>' : $author;
        }

        return $this->parseAuthorString($this->ask('Author (User <user@email.com>)', $author));
    }

    /**
     * Gets the author string from environment.
     *
     * @return string
     */
    protected function getAuthorString()
    {
        $git = $this->getGitConfig();

        if (!empty($_SERVER['COMPOSER_DEFAULT_AUTHOR'])) {
            $author = $_SERVER['COMPOSER_DEFAULT_AUTHOR'];
        } elseif (isset($git['user.name'])) {
            $author = $git['user.name'];
        }

        if (!empty($_SERVER['COMPOSER_DEFAULT_EMAIL'])) {
            $email = $_SERVER['COMPOSER_DEFAULT_EMAIL'];
        } elseif (isset($git['user.email'])) {
            $email = $git['user.email'];
        }

        return isset($author) && isset($email) ? sprintf('%s <%s>', $author, $email) : '';
    }

    /**
     * Parse author string into author info array.
     *
     * @param  string $author
     * @return array
     */
    protected function parseAuthorString($author)
    {
        if (preg_match('/^(?P<name>[- .,\p{L}\p{N}\p{Mn}\'’"()]+) <(?P<email>.+?)>$/u', $author, $match)) {
            if ($this->isValidEmail($match['email'])) {
                return array(
                    'name' => trim($match['name']),
                    'email' => $match['email'],
                );
            }
        }

        throw new \InvalidArgumentException(
            'Invalid author string.  Must be in the format: John Smith <john@example.com>'
        );
    }

    /**
     * Validates whether the value is a valid e-mail address.
     *
     * @param $email
     * @return bool
     */
    protected function isValidEmail($email)
    {
        // assume it's valid if we can't validate it
        if (!function_exists('filter_var')) {
            return true;
        }

        // php <5.3.3 has a very broken email validator, so bypass checks
        if (PHP_VERSION_ID < 50303) {
            return true;
        }

        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Gets the package root namespace
     *
     * @param string $packageName
     * @return array|bool|null|string
     */
    protected function getPackageRootNamespace($packageName = '')
    {
        $namespace = $this->option('namespace');

        if (empty($namespace) && !empty($packageName)) {
            $namespace = $this->getNamespaceFromPackageName($packageName);
        }

        return $this->ask('Root namespace', $namespace);
    }

    /**
     * Gets the package root namespace from packageName
     *
     * @param string $packageName
     * @return string
     */
    protected function getNamespaceFromPackageName($packageName)
    {
        $packageName = preg_replace('/[^\/A-Za-z0-9_-]/', '', $packageName);
        $packageName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $packageName)));
        return str_replace(' ', '\\', ucwords(str_replace('/', ' ', $packageName)));
    }

    /**
     * Replace package name.
     *
     * @param $composer
     * @param $name
     * @return $this
     */
    protected function replacePackageName(&$composer, $name)
    {
        $composer['name'] = $name;
        return $this;
    }

    /**
     * Replace package description
     *
     * @param $composer
     * @param $description
     * @return $this
     */
    protected function replacePackageDescription(&$composer, $description)
    {
        $composer['description'] = $description;
        return $this;
    }

    /**
     * Replace package author
     *
     * @param $composer
     * @param $author
     * @return $this
     */
    protected function replacePackageAuthor(&$composer, $author)
    {
        $composer['authors'] = [$author];
        return $this;
    }

    /**
     * Replace package namespace
     *
     * @param $composer
     * @param $namespace
     * @return $this
     */
    protected function replacePackageNamespace(&$composer, $namespace)
    {
        $originNamespace = 'Foris\\Easy\\Sdk\\Skeleton';

        foreach ($composer['autoload']['psr-4'] as $key => $value) {
            if ($value == 'src/') {
                $originNamespace = Str::replaceLast('\\', '', $key);
                break;
            }
        }

        $composer['autoload']['psr-4'] = [$namespace . '\\' => 'src/'];
        $composer['autoload-dev']['psr-4'] = [$namespace . '\\Tests\\' => 'tests/'];
        return $this->replaceSdkClassNamespace($originNamespace, $namespace);
    }

    /**
     * Replace sdk class namespace
     *
     * @param $originNamespace
     * @param $namespace
     * @return InitCommand
     */
    protected function replaceSdkClassNamespace($originNamespace, $namespace)
    {
        $basePath = getcwd() . '/' . $this->getDirectoryInput();

        foreach (Filesystem::scanFiles($basePath) as $file) {
            file_put_contents($file, str_replace($originNamespace, $namespace, file_get_contents($file)));
        }

        return $this;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd().'/composer.phar';

        if (file_exists($composerPath)) {
            return '"'.PHP_BINARY.'" '.$composerPath;
        }

        return 'composer';
    }

    /**
     * Run composer:install command
     */
    protected function composerInstall()
    {
        $composer = $this->findComposer();

        $commands = [
            $composer.' install --no-scripts',
            $composer.' dump-autoload',
            $composer.' run-script post-autoload-dump',
        ];

        if ($this->hasOption('no-ansi') && $this->option('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value.' --no-ansi';
            }, $commands);
        }

        if ($this->hasOption('quiet') && $this->option('quiet')) {
            $commands = array_map(function ($value) {
                return $value.' --quiet';
            }, $commands);
        }

        $commandLine = implode(' && ', $commands);
        $cwd = $this->getDirectoryInput();

        if (method_exists(Process::class, 'fromShellCommandline')) {
            $process = call_user_func_array([Process::class, 'fromShellCommandline'], [$commandLine, $cwd, null, null, null]);
        } else {
            $process = new Process($commandLine, $cwd, null, null, null);
        }

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->warn('Warning: '.$e->getMessage());
            }
        }

        $process->run();

        if ($process->isSuccessful()) {
            $this->line('<comment>Application ready! Build something amazing.</comment>');
        }
    }
}
