<?php

namespace BagistoPlus\Visual;

use BagistoPlus\Visual\Exceptions\JsonSchemaValidationException;
use Swaggest\JsonSchema\Context;
use Swaggest\JsonSchema\RemoteRef\Preloaded;
use Swaggest\JsonSchema\Schema;

/**
 * Class JsonSchemaValidator
 *
 * Provides validation for JSON files against specific schema definitions.
 */
class JsonSchemaValidator
{
    /**
     * Validate a section schema.
     *
     * @param  string  $schemaPath  The path to the section schema file.
     *
     * @throws JsonSchemaValidationException If validation fails.
     */
    public static function validateSectionSchema(string $schemaPath)
    {
        self::validate($schemaPath, __DIR__.'/../resources/schemas/section.schema.json');
    }

    /**
     * Validate a theme settings schema.
     *
     * @param  string  $schemaPath  The path to the theme settings schema file.
     *
     * @throws JsonSchemaValidationException If validation fails.
     */
    public static function validateThemeSettingsSchema(string $schemaPath)
    {
        self::validate($schemaPath, __DIR__.'/../resources/schemas/theme-settings.schema.json');
    }

    /**
     * Generic method to validate a data file against a meta-schema.
     *
     * @param  string  $dataPath  The path to the JSON data file.
     * @param  string  $metaSchemaPath  The path to the schema file used for validation.
     *
     * @throws JsonSchemaValidationException If validation fails.
     */
    public static function validate(string $dataPath, string $metaSchemaPath)
    {
        $data = json_decode(file_get_contents($dataPath));
        $schema = json_decode(file_get_contents($metaSchemaPath));

        $context = new Context;
        $context->remoteRefProvider = new Preloaded;
        $context->remoteRefProvider->setSchemaData(
            'file://definitions.schema.json',
            json_decode(file_get_contents(__DIR__.'/../resources/schemas/definitions.schema.json'))
        );

        try {
            $validator = Schema::import($schema, $context);
            $validator->in($data);
        } catch (\Swaggest\JsonSchema\Exception\ObjectException $e) {
            throw JsonSchemaValidationException::invalid($dataPath, $e);
        }
    }
}
