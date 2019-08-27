<?php
/**
 * Ausbildungsgangabschnitt
 *
 * PHP version 5
 *
 * @category Class
 * @package  Swagger\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * VEDA Bildungsmanager API
 *
 * Dokumentation der REST-Schnittstellen des VEDA Bildungsmanagers für die Version 2. Die Dokumentation zu speziellen Versionen kann über die Angabe des zusätzlichen Parameters \"group\" angezeigt werden. Beispiel: .../api/docs?group=v1 für die Dokumentation der Version 1, die aktuelle Version ist unter .../api/docs erreichbar.
 *
 * OpenAPI spec version: 2
 * Contact: info@veda.net
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.8
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace Swagger\Client\Model;

use \ArrayAccess;
use \Swagger\Client\ObjectSerializer;

/**
 * Ausbildungsgangabschnitt Class Doc Comment
 *
 * @category Class
 * @description Ausbildungsgangabschnitt mit Details wie Reihenfolge und Art.
 * @package  Swagger\Client
 * @author   Swagger Codegen team
 * @link     https://github.com/swagger-api/swagger-codegen
 */
class Ausbildungsgangabschnitt implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'Ausbildungsgangabschnitt';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'oid' => 'string',
        'ausbildungsgangabschnittsart' => 'string',
        'bezeichnung' => 'string',
        'kurz_bezeichnung' => 'string',
        'links' => '\Swagger\Client\Model\Link[]',
        'reihenfolge' => 'int'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'oid' => null,
        'ausbildungsgangabschnittsart' => null,
        'bezeichnung' => null,
        'kurz_bezeichnung' => null,
        'links' => null,
        'reihenfolge' => 'int32'
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'oid' => 'oid',
        'ausbildungsgangabschnittsart' => 'ausbildungsgangabschnittsart',
        'bezeichnung' => 'bezeichnung',
        'kurz_bezeichnung' => 'kurzBezeichnung',
        'links' => 'links',
        'reihenfolge' => 'reihenfolge'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'oid' => 'setOid',
        'ausbildungsgangabschnittsart' => 'setAusbildungsgangabschnittsart',
        'bezeichnung' => 'setBezeichnung',
        'kurz_bezeichnung' => 'setKurzBezeichnung',
        'links' => 'setLinks',
        'reihenfolge' => 'setReihenfolge'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'oid' => 'getOid',
        'ausbildungsgangabschnittsart' => 'getAusbildungsgangabschnittsart',
        'bezeichnung' => 'getBezeichnung',
        'kurz_bezeichnung' => 'getKurzBezeichnung',
        'links' => 'getLinks',
        'reihenfolge' => 'getReihenfolge'
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
        return self::$swaggerModelName;
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
        $this->container['oid'] = isset($data['oid']) ? $data['oid'] : null;
        $this->container['ausbildungsgangabschnittsart'] = isset($data['ausbildungsgangabschnittsart']) ? $data['ausbildungsgangabschnittsart'] : null;
        $this->container['bezeichnung'] = isset($data['bezeichnung']) ? $data['bezeichnung'] : null;
        $this->container['kurz_bezeichnung'] = isset($data['kurz_bezeichnung']) ? $data['kurz_bezeichnung'] : null;
        $this->container['links'] = isset($data['links']) ? $data['links'] : null;
        $this->container['reihenfolge'] = isset($data['reihenfolge']) ? $data['reihenfolge'] : null;
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
        if ($this->container['ausbildungsgangabschnittsart'] === null) {
            $invalidProperties[] = "'ausbildungsgangabschnittsart' can't be null";
        }
        if ($this->container['bezeichnung'] === null) {
            $invalidProperties[] = "'bezeichnung' can't be null";
        }
        if ($this->container['kurz_bezeichnung'] === null) {
            $invalidProperties[] = "'kurz_bezeichnung' can't be null";
        }
        if ($this->container['reihenfolge'] === null) {
            $invalidProperties[] = "'reihenfolge' can't be null";
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
     * @return $this
     */
    public function setOid($oid)
    {
        $this->container['oid'] = $oid;

        return $this;
    }

    /**
     * Gets ausbildungsgangabschnittsart
     *
     * @return string
     */
    public function getAusbildungsgangabschnittsart()
    {
        return $this->container['ausbildungsgangabschnittsart'];
    }

    /**
     * Sets ausbildungsgangabschnittsart
     *
     * @param string $ausbildungsgangabschnittsart Art des Ausbildungsgangabschnitts
     *
     * @return $this
     */
    public function setAusbildungsgangabschnittsart($ausbildungsgangabschnittsart)
    {
        $this->container['ausbildungsgangabschnittsart'] = $ausbildungsgangabschnittsart;

        return $this;
    }

    /**
     * Gets bezeichnung
     *
     * @return string
     */
    public function getBezeichnung()
    {
        return $this->container['bezeichnung'];
    }

    /**
     * Sets bezeichnung
     *
     * @param string $bezeichnung Bezeichnung des Ausbildungsgangabschnitt
     *
     * @return $this
     */
    public function setBezeichnung($bezeichnung)
    {
        $this->container['bezeichnung'] = $bezeichnung;

        return $this;
    }

    /**
     * Gets kurz_bezeichnung
     *
     * @return string
     */
    public function getKurzBezeichnung()
    {
        return $this->container['kurz_bezeichnung'];
    }

    /**
     * Sets kurz_bezeichnung
     *
     * @param string $kurz_bezeichnung Kurzbezeichnung des Ausbildungsgangabschnitt
     *
     * @return $this
     */
    public function setKurzBezeichnung($kurz_bezeichnung)
    {
        $this->container['kurz_bezeichnung'] = $kurz_bezeichnung;

        return $this;
    }

    /**
     * Gets links
     *
     * @return \Swagger\Client\Model\Link[]
     */
    public function getLinks()
    {
        return $this->container['links'];
    }

    /**
     * Sets links
     *
     * @param \Swagger\Client\Model\Link[] $links links
     *
     * @return $this
     */
    public function setLinks($links)
    {
        $this->container['links'] = $links;

        return $this;
    }

    /**
     * Gets reihenfolge
     *
     * @return int
     */
    public function getReihenfolge()
    {
        return $this->container['reihenfolge'];
    }

    /**
     * Sets reihenfolge
     *
     * @param int $reihenfolge Nummer zur Bestimmung der Reihenfolge
     *
     * @return $this
     */
    public function setReihenfolge($reihenfolge)
    {
        $this->container['reihenfolge'] = $reihenfolge;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
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
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


