<?php

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;

class Type extends FieldType
{
    const TYPE_IDENTIFIER = 'query';

    protected $validatorConfigurationSchema = array();

    protected $settingsSchema = [
        'QueryType' => ['type' => 'string', 'default' => ''],
        'Parameters' => ['type' => 'string', 'default' => ''],
        'ReturnedType' => ['type' => 'string', 'default' => ''],
    ];


    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = [];

        return $validationErrors;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        // @todo should inspect $fieldValue->items to check if the fields match the field definition
        $validationErrors = [];

        return $validationErrors;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return self::TYPE_IDENTIFIER;
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string)$value->text;
    }

    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value)
    {
        return empty($value->items);
    }

    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if (!is_array($value->items)) {
            throw new InvalidArgumentType(
                '$value->items',
                'array',
                $value->items
            );
        }
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \eZ\Publish\Core\FieldType\TextLine\Value $value
     *
     * @return array
     */
    protected function getSortInfo(BaseValue $value)
    {
        return $this->transformationProcessor->transformByGroup((string)$value, 'lowercase');
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return $value->items;
    }

    /**
     * Returns whether the field type is searchable.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }

    public function validateFieldSettings($fieldSettings)
    {
        $errors = [];

        if (isset($fieldSettings['QueryType'])) {
            /**
             * $errors[] = new ValidationError("Query type %query_type does not exist", null, ['%query_type%' => $fieldSettings['QueryType']]);
             */
        }

        if (isset($fieldSettings['Parameters']) && $fieldSettings['Parameters']) {
            if (json_decode($fieldSettings['Parameters']) === null) {
                $errors[] = new ValidationError("Parameters is not a valid json structure");
            }
        }

         return $errors;
    }

    /**
     * @param \BD\EzPlatformQueryFieldType\FieldType\Query\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        return new PersistenceValue(['externalData' => $value->items]);
    }
}
