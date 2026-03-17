<?php

namespace SymfonyTools\DocsBuilder\GuidesExtension\Tests;

use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SymfonyTools\DocsBuilder\GuidesExtension\Build\BuildConfig;
use SymfonyTools\DocsBuilder\GuidesExtension\Build\DynamicBuildEnvironment;
use SymfonyTools\DocsBuilder\GuidesExtension\DocBuilder;
use SymfonyTools\DocsBuilder\GuidesExtension\DocsKernel;
use phpDocumentor\Guides\DependencyInjection\TestExtension;

class JsonIntegrationTest extends TestCase
{
    #[DataProvider('getJsonTests')]
    public function testJsonGeneration(string $filename, array $expectedData)
    {
        $kernel = DocsKernel::create([new TestExtension()]);

        $kernel->get(BuildConfig::class)->outputFormat = 'json';

        $buildEnvironment = new DynamicBuildEnvironment(new LocalFilesystemAdapter(__DIR__.'/fixtures/source/json'));
        $kernel->get(DocBuilder::class)->build($buildEnvironment);

        $actualFileData = json_decode($buildEnvironment->getOutputFilesystem()->read($filename.'.fjson'), true);
        $this->assertSame($expectedData, array_intersect_key($actualFileData, $expectedData), sprintf('Invalid data in file "%s"', $filename));
        foreach ($expectedData as $key => $expectedKeyData) {
            $this->assertArrayHasKey($key, $actualFileData, sprintf('Missing key "%s" in file "%s"', $key, $filename));
        }
    }

    public static function getJsonTests()
    {
        yield 'index' => [
            'filename' => 'index',
            'expectedData' => [
                'parents' => [],
                'prev' => null,
                'next' => [
                    'title' => 'Dashboards',
                    'link' => 'dashboards.html',
                ],
                'title' => 'JSON Generation Test',
            ]
        ];

        yield 'dashboards' => [
            'filename' => 'dashboards',
            'expectedData' => [
                'parents' => [],
                'prev' => [
                    'title' => 'JSON Generation Test',
                    'link' => 'index.html',
                ],
                'next' => [
                    'title' => 'Design',
                    'link' => 'design.html',
                ],
                'title' => 'Dashboards',
            ]
        ];

        yield 'design' => [
            'filename' => 'design',
            'expectedData' => [
                'parents' => [],
                'prev' => [
                    'title' => 'Dashboards',
                    'link' => 'dashboards.html',
                ],
                'next' => [
                    'title' => 'CRUD',
                    'link' => 'crud.html',
                ],
                'title' => 'Design',
                'toc_options' => [
                    'maxDepth' => 2,
                    'numVisibleItems' => 5,
                    'size' => 'md'
                ],
                'toc' => [
                    [
                        'level' => 1,
                        'url' => 'design.html#section-1',
                        'page' => 'design',
                        'fragment' => 'section-1',
                        'title' => 'Section 1',
                        'children' => [
                            [
                                'level' => 2,
                                'url' => 'design.html#some-subsection',
                                'page' => 'design',
                                'fragment' => 'some-subsection',
                                'title' => 'Some subsection',
                                'children' => [],
                            ],
                            [
                                'level' => 2,
                                'url' => 'design.html#some-subsection-1',
                                'page' => 'design',
                                'fragment' => 'some-subsection-1',
                                'title' => 'Some subsection',
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'level' => 1,
                        'url' => 'design.html#section-2',
                        'page' => 'design',
                        'fragment' => 'section-2',
                        'title' => 'Section 2',
                        'children' => [
                            [
                                'level' => 2,
                                'url' => 'design.html#some-subsection-2',
                                'page' => 'design',
                                'fragment' => 'some-subsection-2',
                                'title' => 'Some subsection',
                                'children' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield 'crud' => [
           'filename' => 'crud',
           'expectedData' => [
               'parents' => [],
               'prev' => [
                   'title' => 'Design',
                   'link' => 'design.html',
               ],
               'next' => [
                   'title' => 'Fields',
                   'link' => 'fields.html',
               ],
               'title' => 'CRUD',
           ]
       ];

        yield 'design/sub-page' => [
            'filename' => 'design/sub-page',
            'expectedData' => [
                'parents' => [
                    [
                        'title' => 'Design',
                        'link' => '../design.html',
                    ],
                ],
                'title' => 'Design Sub-Page',
            ]
        ];

        yield 'fields' => [
           'filename' => 'fields',
           'expectedData' => [
               'parents' => [],
               'prev' => [
                   'title' => 'CRUD',
                   'link' => 'crud.html',
               ],
               'next' => null,
               'title' => 'Fields',
           ]
       ];

        yield 'orphan' => [
          'filename' => 'orphan',
          'expectedData' => [
              'parents' => [],
              'prev' => null,
              'next' => null,
              'title' => 'Orphan',
          ]
      ];
    }
}
