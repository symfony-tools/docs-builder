<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Release\Exception;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class ReleaseFailed extends \RuntimeException
{
    public static function whileCreatingDraft(HttpExceptionInterface $previous): self
    {
        if (401 === $previous->getCode()) {
            $message = 'Error while trying to create release: Invalid token.';
        } else {
            $message = 'Error while trying to create release.';
        }

        return new self($message, 0, $previous);
    }

    public static function whileAttachingAssetToRelease(HttpExceptionInterface $previous): self
    {
        return new self('Error while adding asset to release.', 0, $previous);
    }

    public static function whilePublishingRelease(HttpExceptionInterface $previous): self
    {
        return new self('Error while publishing release. Maybe the tag name already exists?', 0, $previous);
    }

    public function toString(): string
    {
        return sprintf(
            "Failed to create a new release: [%s]\n\t\"%s\"\n\tat %s:%s\n\nHttpException was: [%s]\n\t\"%s\"\n\tat %s:%s\n",
            self::class,
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            get_class($this->getPrevious()),
            $this->getPrevious()->getMessage(),
            $this->getPrevious()->getFile(),
            $this->getPrevious()->getLine()
        );
    }
}
