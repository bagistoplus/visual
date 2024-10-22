<?php

namespace BagistoPlus\Visual\Exceptions;

use Swaggest\JsonSchema\Exception\ObjectException;

class JsonSchemaValidationException extends ObjectException
{
    protected $dataPath;

    public function __construct(string $message, string $dataPath, int $code = 0, ?\Throwable $previous = null)
    {
        $this->dataPath = $dataPath;
        parent::__construct($message, $code, $previous);
    }

    public static function invalid(string $schemaPath, ObjectException $e)
    {
        return new self("Failed to validate '{$schemaPath}':\n{$e->getMessage()}", $schemaPath, $e->getCode(), $e);
    }
}
