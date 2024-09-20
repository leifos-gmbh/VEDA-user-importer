<?php
/**
 * Veranstaltungsanbieter
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * VEDA Bildungsmanager API
 *
 * Dokumentation der REST-Schnittstellen des VEDA Bildungsmanagers für die Version 2. Die Dokumentation zu speziellen Versionen kann über die Angabe des zusätzlichen Parameters \"group\" angezeigt werden. Beispiel: .../api/docs?group=v1 für die Dokumentation der Version 1, die aktuelle Version ist unter .../api/docs erreichbar.
 *
 * The version of the OpenAPI document: 2
 * Contact: info@veda.net
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 6.6.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace OpenAPI\Client\Model;

use \ArrayAccess;
use \OpenAPI\Client\ObjectSerializer;

/**
 * Veranstaltungsanbieter Class Doc Comment
 *
 * @category Class
 * @description Die Informationen eines Veranstaltungsanbieters einer Veranstaltung.
 * @package  OpenAPI\Client
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class Veranstaltungsanbieter implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'Veranstaltungsanbieter';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'oid' => 'string',
        'admin_e_mail' => 'string',
        'agb_api_dto' => '\OpenAPI\Client\Model\AGB',
        'bezeichnung' => 'string',
        'links' => '\OpenAPI\Client\Model\Links',
        'name1' => 'string',
        'name2' => 'string',
        'name3' => 'string',
        'telefonnr' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'oid' => null,
        'admin_e_mail' => null,
        'agb_api_dto' => null,
        'bezeichnung' => null,
        'links' => null,
        'name1' => null,
        'name2' => null,
        'name3' => null,
        'telefonnr' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static array $openAPINullables = [
        'oid' => false,
		'admin_e_mail' => false,
		'agb_api_dto' => false,
		'bezeichnung' => false,
		'links' => false,
		'name1' => false,
		'name2' => false,
		'name3' => false,
		'telefonnr' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected array $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'oid' => 'oid',
        'admin_e_mail' => 'adminEMail',
        'agb_api_dto' => 'agbApiDto',
        'bezeichnung' => 'bezeichnung',
        'links' => 'links',
        'name1' => 'name1',
        'name2' => 'name2',
        'name3' => 'name3',
        'telefonnr' => 'telefonnr'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'oid' => 'setOid',
        'admin_e_mail' => 'setAdminEMail',
        'agb_api_dto' => 'setAgbApiDto',
        'bezeichnung' => 'setBezeichnung',
        'links' => 'setLinks',
        'name1' => 'setName1',
        'name2' => 'setName2',
        'name3' => 'setName3',
        'telefonnr' => 'setTelefonnr'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'oid' => 'getOid',
        'admin_e_mail' => 'getAdminEMail',
        'agb_api_dto' => 'getAgbApiDto',
        'bezeichnung' => 'getBezeichnung',
        'links' => 'getLinks',
        'name1' => 'getName1',
        'name2' => 'getName2',
        'name3' => 'getName3',
        'telefonnr' => 'getTelefonnr'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('oid', $data ?? [], null);
        $this->setIfExists('admin_e_mail', $data ?? [], null);
        $this->setIfExists('agb_api_dto', $data ?? [], null);
        $this->setIfExists('bezeichnung', $data ?? [], null);
        $this->setIfExists('links', $data ?? [], null);
        $this->setIfExists('name1', $data ?? [], null);
        $this->setIfExists('name2', $data ?? [], null);
        $this->setIfExists('name3', $data ?? [], null);
        $this->setIfExists('telefonnr', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['oid'] === null) {
            $invalidProperties[] = "'oid' can't be null";
        }
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets oid
     *
     * @return string
     */
    public function getOid()
    {
        return $this->container['oid'];
    }

    /**
     * Sets oid
     *
     * @param string $oid UUID des Datensatzes
     *
     * @return self
     */
    public function setOid($oid)
    {
        if (is_null($oid)) {
            throw new \InvalidArgumentException('non-nullable oid cannot be null');
        }
        $this->container['oid'] = $oid;

        return $this;
    }

    /**
     * Gets admin_e_mail
     *
     * @return string|null
     */
    public function getAdminEMail()
    {
        return $this->container['admin_e_mail'];
    }

    /**
     * Sets admin_e_mail
     *
     * @param string|null $admin_e_mail Administrative E-Mail-Adresse des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setAdminEMail($admin_e_mail)
    {
        if (is_null($admin_e_mail)) {
            throw new \InvalidArgumentException('non-nullable admin_e_mail cannot be null');
        }
        $this->container['admin_e_mail'] = $admin_e_mail;

        return $this;
    }

    /**
     * Gets agb_api_dto
     *
     * @return \OpenAPI\Client\Model\AGB|null
     */
    public function getAgbApiDto()
    {
        return $this->container['agb_api_dto'];
    }

    /**
     * Sets agb_api_dto
     *
     * @param \OpenAPI\Client\Model\AGB|null $agb_api_dto agb_api_dto
     *
     * @return self
     */
    public function setAgbApiDto($agb_api_dto)
    {
        if (is_null($agb_api_dto)) {
            throw new \InvalidArgumentException('non-nullable agb_api_dto cannot be null');
        }
        $this->container['agb_api_dto'] = $agb_api_dto;

        return $this;
    }

    /**
     * Gets bezeichnung
     *
     * @return string|null
     */
    public function getBezeichnung()
    {
        return $this->container['bezeichnung'];
    }

    /**
     * Sets bezeichnung
     *
     * @param string|null $bezeichnung Bezeichnung des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setBezeichnung($bezeichnung)
    {
        if (is_null($bezeichnung)) {
            throw new \InvalidArgumentException('non-nullable bezeichnung cannot be null');
        }
        $this->container['bezeichnung'] = $bezeichnung;

        return $this;
    }

    /**
     * Gets links
     *
     * @return \OpenAPI\Client\Model\Links|null
     */
    public function getLinks()
    {
        return $this->container['links'];
    }

    /**
     * Sets links
     *
     * @param \OpenAPI\Client\Model\Links|null $links links
     *
     * @return self
     */
    public function setLinks($links)
    {
        if (is_null($links)) {
            throw new \InvalidArgumentException('non-nullable links cannot be null');
        }
        $this->container['links'] = $links;

        return $this;
    }

    /**
     * Gets name1
     *
     * @return string|null
     */
    public function getName1()
    {
        return $this->container['name1'];
    }

    /**
     * Sets name1
     *
     * @param string|null $name1 Name 1 der Organisation des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setName1($name1)
    {
        if (is_null($name1)) {
            throw new \InvalidArgumentException('non-nullable name1 cannot be null');
        }
        $this->container['name1'] = $name1;

        return $this;
    }

    /**
     * Gets name2
     *
     * @return string|null
     */
    public function getName2()
    {
        return $this->container['name2'];
    }

    /**
     * Sets name2
     *
     * @param string|null $name2 Name 2 der Organisation des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setName2($name2)
    {
        if (is_null($name2)) {
            throw new \InvalidArgumentException('non-nullable name2 cannot be null');
        }
        $this->container['name2'] = $name2;

        return $this;
    }

    /**
     * Gets name3
     *
     * @return string|null
     */
    public function getName3()
    {
        return $this->container['name3'];
    }

    /**
     * Sets name3
     *
     * @param string|null $name3 Name 3 der Organisation des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setName3($name3)
    {
        if (is_null($name3)) {
            throw new \InvalidArgumentException('non-nullable name3 cannot be null');
        }
        $this->container['name3'] = $name3;

        return $this;
    }

    /**
     * Gets telefonnr
     *
     * @return string|null
     */
    public function getTelefonnr()
    {
        return $this->container['telefonnr'];
    }

    /**
     * Sets telefonnr
     *
     * @param string|null $telefonnr Telefonnummer des Veranstaltungsanbieters
     *
     * @return self
     */
    public function setTelefonnr($telefonnr)
    {
        if (is_null($telefonnr)) {
            throw new \InvalidArgumentException('non-nullable telefonnr cannot be null');
        }
        $this->container['telefonnr'] = $telefonnr;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


