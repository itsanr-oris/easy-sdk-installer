<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Sdk\Tests\Installer;

use Foris\Easy\Sdk\Installer\Application;
use Foris\Easy\Sdk\Installer\Commands\InitCommand;
use Foris\Easy\Sdk\Installer\Commands\NewCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CreateSdkPackageTest
 */
class CommandTest extends TestCase
{
    /**
     * Test create a new sdk project
     */
    public function testCreateANewSdkProject()
    {
        // clean up test out directory.
        $name = 'tests-output/my-sdk';
        $path = __DIR__ . '/../' . $name;

        if (file_exists($path)) {
            (new Filesystem())->remove($path);
        }

        $mockApplication = \Mockery::mock(Application::class)->makePartial();
        $mockApplication->shouldReceive('call')->andReturnTrue();

        $command = new NewCommand();
        $command->setApplication($mockApplication);

        $tester = new CommandTester($command);
        $statusCode = $tester->execute(['name' => $name]);

        $this->assertEquals($statusCode, 0);
        $this->assertFileExists($path);
        $this->assertFileExists($path . '/composer.json');
    }

    /**
     * Test init sdk project
     *
     * @depends testCreateANewSdkProject
     */
    public function testInitSdkProject()
    {
        $name = 'tests-output/my-sdk';
        $path = __DIR__ . '/../' . $name;

        $command = new InitCommand();
        $tester = new CommandTester($command);

        $statusCode = $tester->execute([
            'directory' => $name,
            '--name' => 'f-oris/my-sdk',
            '--description' => 'my-sdk description',
            '--author' => 'F.oris <us@f-oris.me>',
            '--namespace' => 'Foris\MySdk',
        ]);

        $this->assertEquals($statusCode, 0);
        $this->assertFileExists($path . '/vendor');

        $composer = json_decode(file_get_contents($path . '/composer.json'), true);
        $this->assertEquals($composer['name'], 'f-oris/my-sdk');
        $this->assertEquals($composer['description'], 'my-sdk description');
        $this->assertEquals($composer['authors'], [['name' => 'F.oris', 'email' => 'us@f-oris.me']]);
        $this->assertArrayHasKey( 'Foris\\MySdk\\', $composer['autoload']['psr-4']);
        $this->assertArrayHasKey('Foris\\MySdk\\Tests\\', $composer['autoload-dev']['psr-4']);

        $this->assertFileExists($path . '/vendor/autoload.php');
        require_once $path . '/vendor/autoload.php';

        $this->assertTrue(class_exists('Foris\MySdk\Application'));
    }
}
