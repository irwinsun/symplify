<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Finder;

use Symfony\Component\Finder\Finder;
use Symplify\EasyCodingStandard\Git\GitDiffProvider;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Symplify\EasyCodingStandard\Tests\Finder\SourceFinderTest
 */
final class SourceFinder
{
    /**
     * @var string[]
     */
    private array $fileExtensions = [];

    public function __construct(
        private FinderSanitizer $finderSanitizer,
        ParameterProvider $parameterProvider,
        private GitDiffProvider $gitDiffProvider
    ) {
        $this->fileExtensions = $parameterProvider->provideArrayParameter(Option::FILE_EXTENSIONS);
    }

    /**
     * @param string[] $source
     * @param bool $doesMatchGitDiff - @deprecated
     * @return SmartFileInfo[]
     */
    public function find(array $source, bool $doesMatchGitDiff = false): array
    {
        $fileInfos = [];
        foreach ($source as $singleSource) {
            if (is_file($singleSource)) {
                $fileInfos[] = new SmartFileInfo($singleSource);
            } else {
                $filesInDirectory = $this->processDirectory($singleSource);
                $fileInfos = array_merge($fileInfos, $filesInDirectory);
            }
        }

        $fileInfos = $this->filterOutGitDiffFiles($fileInfos, $doesMatchGitDiff);

        ksort($fileInfos);

        return $fileInfos;
    }

    /**
     * @return SmartFileInfo[]
     */
    private function processDirectory(string $directory): array
    {
        $normalizedFileExtensions = $this->normalizeFileExtensions($this->fileExtensions);

        $finder = Finder::create()
            ->files()
            ->name($normalizedFileExtensions)
            ->in($directory)
            ->exclude('vendor')
            // skip empty files
            ->size('> 0')
            ->sortByName();

        return $this->finderSanitizer->sanitize($finder);
    }

    /**
     * @param string[] $fileExtensions
     * @return string[]
     */
    private function normalizeFileExtensions(array $fileExtensions): array
    {
        $normalizedFileExtensions = [];

        foreach ($fileExtensions as $fileExtension) {
            $normalizedFileExtensions[] = '*.' . $fileExtension;
        }

        return $normalizedFileExtensions;
    }

    /**
     * @param SmartFileInfo[] $fileInfos
     * @param bool $doesMatchGitDiff @deprecated
     * @return SmartFileInfo[]
     */
    private function filterOutGitDiffFiles(array $fileInfos, bool $doesMatchGitDiff): array
    {
        if (! $doesMatchGitDiff) {
            return $fileInfos;
        }

        $gitDiffFiles = $this->gitDiffProvider->provide();

        $fileInfos = array_filter(
            $fileInfos,
            fn ($splFile): bool => in_array($splFile->getRealPath(), $gitDiffFiles, true)
        );

        return array_values($fileInfos);
    }
}
