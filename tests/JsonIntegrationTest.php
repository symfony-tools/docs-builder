<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests;

use SymfonyDocsBuilder\DocBuilder;

class JsonIntegrationTest extends AbstractIntegrationTest
{
    /**
     * @dataProvider getJsonTests
     */
    public function testJsonGeneration(string $filename, array $expectedData)
    {
        $buildConfig = $this->createBuildConfig(__DIR__ . '/fixtures/source/json');
        $builder = new DocBuilder();
        $buildResult = $builder->build($buildConfig);
        $fJsons = $buildResult->getJsonResults();

        $actualFileData = $fJsons[$filename];
        foreach ($expectedData as $key => $expectedKeyData) {
            $this->assertArrayHasKey($key, $actualFileData, sprintf('Missing key "%s" in file "%s"', $key, $filename));
            $this->assertSame($expectedData[$key], $actualFileData[$key], sprintf('Invalid data for key "%s" in file "%s"', $key, $filename));
        }
    }

    public function getJsonTests()
    {
        yield 'index' => [
            'file' => 'index',
            'data' => [
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
            'file' => 'dashboards',
            'data' => [
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
            'file' => 'design',
            'data' => [
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
            ]
        ];

        yield 'crud' => [
           'file' => 'crud',
           'data' => [
               'parents' => [
                    [
                        'title' => 'Design',
                        'link' => 'design.html',
                    ],
                ],
               'prev' => [
                   'title' => 'Design',
                   'link' => 'design.html',
               ],
               'next' => [
                   'title' => 'Design Sub-Page',
                   'link' => 'design/sub-page.html',
               ],
               'title' => 'CRUD',
           ]
       ];

        yield 'design/sub-page' => [
            'file' => 'design/sub-page',
            'data' => [
                'parents' => [
                    [
                        'title' => 'Design',
                        'link' => '../design.html',
                    ],
                ],
                'prev' => [
                    'title' => 'CRUD',
                    'link' => '../crud.html',
                ],
                'next' => [
                    'title' => 'Fields',
                    'link' => '../fields.html',
                ],
                'title' => 'Design Sub-Page',
            ]
        ];

        yield 'fields' => [
           'file' => 'fields',
           'data' => [
               'parents' => [],
               'prev' => [
                   'title' => 'Design Sub-Page',
                   'link' => 'design/sub-page.html',
               ],
               'next' => null,
               'title' => 'Fields',
           ]
       ];

        yield 'orphan' => [
          'file' => 'orphan',
          'data' => [
              'parents' => [],
              'prev' => null,
              'next' => null,
              'title' => 'Orphan',
          ]
      ];
    }
}
