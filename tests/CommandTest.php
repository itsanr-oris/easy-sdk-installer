<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Sdk\Tests\Installer;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CreateSdkPackageTest
 */
class CommandTest extends TestCase
{
    /**
     * Test make a new sdk project
     *
     * @throws \Exception
     */
    public function testMakeSdkProject()
    {
        $name = 'tests-output/sdk-demo';
        $path = __DIR__ . '/../' . $name;

        if (file_exists($path)) {
            (new Filesystem())->remove($path);
        }

        $this->setInteractive(true);
        $this->setInputs(['', '', '', '']);
        $this->call('new', ['name' => $name]);

        $this->assertFileExists($path . '/composer.json');
        $composer = json_decode(file_get_contents($path . '/composer.json'), true);

        $this->assertArrayHasKey('f-oris/easy-sdk-framework', $composer['require']);
    }

    /**
     * Test init a sdk project.
     *
     * @throws \Exception
     * @depends testMakeSdkProject
     */
    public function testInitSdkProject()
    {
        $name = 'tests-output/sdk-demo';
        $path = __DIR__ . '/../' . $name;

        $this->setInteractive(true);
        $this->setInputs(['sdk/demo', 'demo sdk', 'f-oris <us@f-oris.me>', 'Sdk\\Demo']);
        $this->call('init', ['directory' => $name]);

        $this->assertFileExists($path . '/composer.json');
        $composer = json_decode(file_get_contents($path . '/composer.json'), true);

        $this->assertEquals($composer['name'], 'sdk/demo');
        $this->assertEquals($composer['description'], 'demo sdk');
        $this->assertEquals($composer['authors'], [['name' => 'f-oris', 'email' => 'us@f-oris.me']]);
        $this->assertArrayHasKey( 'Sdk\\Demo\\', $composer['autoload']['psr-4']);
        $this->assertArrayHasKey('Sdk\\Demo\\Tests\\', $composer['autoload-dev']['psr-4']);

        $this->assertFileExists($path . '/vendor/autoload.php');
        require_once $path . '/vendor/autoload.php';

        $this->assertTrue(class_exists('Sdk\Demo\Application'));
    }
}
