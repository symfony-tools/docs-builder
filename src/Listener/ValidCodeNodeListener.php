<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Listener;

use Doctrine\RST\ErrorManager;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Nodes\CodeNode;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Source;

/**
 * Verify that all code nodes has the correct syntax.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ValidCodeNodeListener
{
    private $errorManager;
    private $twig;

    public function __construct(ErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    public function preNodeRender(PreNodeRenderEvent $event)
    {
        $node = $event->getNode();
        if (!$node instanceof CodeNode) {
            return;
        }

        $language = $node->getLanguage() ?? ($node->isRaw() ? null : 'php');
        if (in_array($language, ['php', 'php-symfony', 'php-standalone', 'php-annotations'])) {
            $this->validatePhp($node);
        } elseif ('yaml' === $language) {
            $this->validateYaml($node);
        } elseif ('xml' === $language) {
            $this->validateXml($node);
        } elseif ('json' === $language) {
            $this->validateJson($node);
        } elseif (in_array($language, ['twig', 'html+twig'])) {
            $this->validateTwig($node);
        }
    }

    private function validatePhp(CodeNode $node)
    {
        $file = sys_get_temp_dir().'/'.uniqid('doc_builder', true).'.php';
        $contents = $node->getValue();
        if (!preg_match('#class [a-zA-Z]+#s', $contents) && preg_match('#(public|protected|private) (\$[a-z]+|function)#s', $contents)) {
            $contents = 'class Foobar {'.$contents.'}';
        }

        // Allow us to use "..." as a placeholder
        $contents = str_replace('...', 'null', $contents);

        file_put_contents($file, '<?php' .PHP_EOL. $contents);

        $process = new Process(['php', '-l', $file]);
        $process->run();
        $process->wait();
        if ($process->isSuccessful()) {
            return;
        }

        $this->errorManager->error(sprintf(
            'Invalid PHP syntax in "%s": %s',
            $node->getEnvironment()->getCurrentFileName(),
            str_replace($file, 'example', $process->getErrorOutput())
        ));
    }

    private function validateXml(CodeNode $node)
    {
        try {
            set_error_handler(static function ($errno, $errstr) {
                throw new \RuntimeException($errstr, $errno);
            });

            try {
                // Remove first comment only. (No multiline)
                $xml = preg_replace('#^<!-- .* -->\n#', '', $node->getValue());
                if ('' !== $xml) {
                    $xmlObject = new \SimpleXMLElement($xml);
                }
            } finally {
                restore_error_handler();
            }
        } catch (\Throwable $e) {
            if ('SimpleXMLElement::__construct(): namespace error : Namespace prefix' === substr($e->getMessage(), 0, 67)) {
                return;
            }
            $this->errorManager->error(sprintf(
                'Invalid Xml in "%s": %s',
                $node->getEnvironment()->getCurrentFileName(),
                $e->getMessage()
            ));
        }
    }

    private function validateYaml(CodeNode $node)
    {
        // Allow us to use "..." as a placeholder
        $contents = str_replace('...', 'null', $node->getValue());
        try {
            Yaml::parse($contents, Yaml::PARSE_CUSTOM_TAGS);
        } catch (ParseException $e) {
            if ('Duplicate key' === substr($e->getMessage(), 0, 13)) {
                return;
            }

            $this->errorManager->error(sprintf(
                'Invalid Yaml in "%s": %s',
                $node->getEnvironment()->getCurrentFileName(),
                $e->getMessage()
            ));
        }
    }

    private function validateTwig(CodeNode $node)
    {
        $twig = $this->twig ?? new Environment(new ArrayLoader());

        try {
            $tokens = $twig->tokenize(new Source($node->getValue(), $node->getEnvironment()->getCurrentFileName()));
            // We cannot parse the TokenStream because we dont have all extensions loaded.
            // $twig->parse($tokens);
        } catch (SyntaxError $e) {
            $this->errorManager->error(sprintf(
                'Invalid Twig syntax: %s',
                $e->getMessage()
            ));
        }
    }

    private function validateJson(CodeNode $node)
    {
        $data = json_decode($node->getValue(), true);
        if (null === $data) {
            $this->errorManager->error(sprintf(
                'Invalid Json in "%s"',
                $node->getEnvironment()->getCurrentFileName()
            ));
        }
    }
}
