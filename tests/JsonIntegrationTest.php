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
                //'parents' => [],
                'prev' => null,
                'next' => [
                    'link' => 'dashboards/',
                    'title' => 'Dashboards'
                ],
                'title' => 'JSON Generation Test',
            ]
        ];

        yield 'dashboards' => [
            'file' => 'dashboards',
            'data' => [
                //'parents' => [],
                'prev' => [
                    'title' => 'JSON Generation Test',
                    'link' => 'index.html',
                ],
                'next' => [
                    'title' => 'CRUD',
                    'link' => 'crud.html',
                ],
                'title' => 'Dashboards',
            ]
        ];

        yield 'crud' => [
           'file' => 'crud',
           'data' => [
               //'parents' => [],
               'prev' => [
                   'title' => 'Dashboards',
                   'link' => 'dashboards.html',
               ],
               'next' => [
                   'title' => 'Design',
                   'link' => 'design.html',
               ],
               'title' => 'CRUD',
           ]
       ];

        yield 'design' => [
           'file' => 'design',
           'data' => [
               //'parents' => [],
               'prev' => [
                   'title' => 'CRUD',
                   'link' => 'crud.html',
               ],
               'next' => null,
               'title' => 'Design',
           ]
       ];
    }
}
