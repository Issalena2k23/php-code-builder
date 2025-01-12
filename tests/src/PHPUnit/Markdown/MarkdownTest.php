<?php


namespace Swaggest\PhpCodeBuilder\Tests\PHPUnit\Markdown;


use Swaggest\JsonSchema\Schema;
use Swaggest\PhpCodeBuilder\Markdown\TypeBuilder;

class MarkdownTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSchema()
    {
        $schemaData = <<<'JSON'
{
    "type": "object",
    "title": "Unit",
    "description": "This is a unit of something.",
    "properties": {
        "manager": {"$ref":"#/definitions/Person"},
        "employees": {"type":"array","items":{"$ref":"#/definitions/Person"}}
    },
    "definitions": {
        "Person": {
            "type": "object",
            "properties": {
                "name": {"type": "string", "description": "Person name.", "enum":["John","Jane"]},
                "age": {"type": "integer", "const": 123},
                "isMale": {"type": "boolean"},
                "partner": {"$ref": "#/definitions/Person"},
                "children": {"type":"array", "items": {"$ref":"#/definitions/Person"}}
            }
        }
    }
}
JSON;
        $schema = Schema::import(json_decode($schemaData));
        $this->assertNotEmpty($schema);

        $tb = new TypeBuilder();

        $typeString = $tb->getTypeString($schema);
        $this->assertSame('[`Unit`](#unit)', $typeString);
        $this->assertSame(<<<'MD'


### <a id="person"></a>Person



|Property  |Type                          |Description |
|----------|------------------------------|------------|
|`name`    |`'John'`, <br>`'Jane'`        |Person name.|
|`age`     |`123`                         |            |
|`isMale`  |`Boolean`                     |            |
|`partner` |[`Person`](#person)           |            |
|`children`|`Array<`[`Person`](#person)`>`|            |


### <a id="unit"></a>Unit
This is a unit of something.



|Property   |Type                          |
|-----------|------------------------------|
|`manager`  |[`Person`](#person)           |
|`employees`|`Array<`[`Person`](#person)`>`|

MD
            , $tb->file);

        $tb->sortTypes();
        $this->assertSame(<<<'MD'
# Types

  * [`Person`](#person)
  * [`Unit`](#unit)



MD
            , $tb->tableOfContents());
    }

    /**
     * @return void
     * @throws \Swaggest\JsonSchema\Exception
     * @throws \Swaggest\JsonSchema\InvalidValue
     * @see https://github.com/swaggest/json-cli/issues/39#issuecomment-1313568814
     */
    function testIssue39() {
        $schemaData = <<<'JSON'
{
    "id": "/Test",
    "anyOf": [
        {
            "type": "object",
            "properties": {
                "test1": {
                    "type": "string"
                }
            }
        },
        {
            "type": "object",
            "properties": {
                "test2": {
                    "type": "integer"
                }
            }
        }
    ]
}
JSON;
        $schema = Schema::import(json_decode($schemaData));
        $this->assertNotEmpty($schema);

        $tb = new TypeBuilder();

        $typeString = $tb->getTypeString($schema);
        $this->assertSame('[`AnyOf0`](#anyof0), [`AnyOf1`](#anyof1)', $typeString);
        $this->assertSame(<<<'MD'


### <a id="anyof0"></a>AnyOf0



|Property|Type    |
|--------|--------|
|`test1` |`String`|


### <a id="anyof1"></a>AnyOf1



|Property|Type    |
|--------|--------|
|`test2` |`Number`|

MD
            , $tb->file);

        $tb->sortTypes();
        $this->assertSame(<<<'MD'
# Types

  * [`AnyOf0`](#anyof0)
  * [`AnyOf1`](#anyof1)



MD
            , $tb->tableOfContents());
    }
}