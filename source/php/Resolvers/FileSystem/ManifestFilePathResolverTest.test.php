<?php

namespace EventManager\Resolvers\FileSystem;

use EventManager\Resolvers\FileSystem\ManifestFilePathResolver;
use EventManager\Services\FileSystem\FileExists;
use EventManager\Services\FileSystem\GetFileContent;
use EventManager\Services\WPService\PluginDirPath;
use Exception;
use PHPUnit\Framework\TestCase;

class ManifestFilePathResolverTest extends TestCase
{
    public function testManifestResolverWasGivenAFaultyJson()
    {
        $manifestFilePath     = 'manifest.json';
        $manifestFileContents = "This is not a json file";
        $fileSystem           = $this->getFileSystem([ $manifestFilePath => $manifestFileContents ]);
        $wpService            = $this->getWpService();
        $nullResolver         = new NullFilePathResolver();
        $resolver             = new ManifestFilePathResolver($manifestFilePath, $fileSystem, $wpService, $nullResolver);

        $this->expectException(Exception::class);

        $resolver->resolve('css/file.css');
    }

    private function getWpService(): PluginDirPath
    {
        return new class implements PluginDirPath {
            public function pluginDirPath(string $file): string
            {
                return 'pluginDirPath';
            }
        };
    }

    public function testManifestResolverReturnsCorrectFilePath()
    {
        $manifestFilePath     = 'manifest.json';
        $manifestFileContents = json_encode([ 'css/file.css' => 'css/file-123.css' ]);
        $fileSystem           = $this->getFileSystem([ $manifestFilePath => $manifestFileContents ]);
        $wpService            = $this->getWpService();
        $nullResolver         = new NullFilePathResolver();
        $resolver             = new ManifestFilePathResolver($manifestFilePath, $fileSystem, $wpService, $nullResolver);

        $resolvedFilePath = $resolver->resolve('css/file.css');

        $this->assertEquals('./css/file-123.css', $resolvedFilePath);
    }

    public function testManifestResolverReturnsCorrectFilePathWhenEntryDoesntExist()
    {
        $manifestFilePath     = 'manifest.json';
        $manifestFileContents = json_encode([ 'css/file.css' => 'css/file-123.css' ]);
        $fileSystem           = $this->getFileSystem([ $manifestFilePath => $manifestFileContents ]);
        $wpService            = $this->getWpService();
        $nullResolver         = new NullFilePathResolver();
        $resolver             = new ManifestFilePathResolver($manifestFilePath, $fileSystem, $wpService, $nullResolver);

        $resolvedFilePath = $resolver->resolve('css/file2.css');

        $this->assertEquals('css/file2.css', $resolvedFilePath);
    }

    private function getFileSystem(array $files): FileExists&GetFileContent
    {
        return new class ($files) implements FileExists, GetFileContent {
            public function __construct(private array $files)
            {
            }

            public function fileExists(string $path): bool
            {
                return array_key_exists($path, $this->files);
            }

            public function getFileContent(string $file): string
            {
                return $this->files[$file];
            }
        };
    }
}
