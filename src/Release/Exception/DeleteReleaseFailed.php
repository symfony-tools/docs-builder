<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Release\Exception;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class DeleteReleaseFailed extends ReleaseFailed
{
    private $originException;

    public function __construct(ReleaseFailed $originException, HttpExceptionInterface $previous)
    {
        $this->originException = $originException;

        parent::__construct('Error while deleting release.', 0, $previous);
    }

    public function toString(): string
    {
        return sprintf(
            "%s\n\nOriginal exception was: [%s]\n\t\"%s\"\n\tat %s:%s\n",
            parent::toString(),
            get_class($this->originException),
            $this->originException->getMessage(),
            $this->originException->getFile(),
            $this->originException->getLine()
        );
    }
}
