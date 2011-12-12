<?php
/**
 * The Attribute class is used for storing and rendering HTML attributes.
 * @ingroup Forms
 */
class Attribute
{
	/**
	 * 
	 */
	public $enabled = true;
	
    /**
     * The attribute.
     */
    protected $attribute;

    /**
     * The value to be attached to the value.
     */
    protected $value;

    /**
     * The constructor of the Attribute.
     *
     * @param $attribute The attribute.
     * @param $value The value to attach to the attribute.
     *
     */
    public function __construct($attribute, $value)
    {
        $this->attribute = $attribute;
        $this->value = $value;
    }

    /**
     * Returs the HTML representation of the attribute.
     * @return string
     */
    public function getHTML()
    {
        return $this->enabled ? "$this->attribute=\"$this->value\"" : "";
    }

    /**
     * Sets the value for the attribute.
     * @return Attribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Gets the value of the attribute.
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Sets the value represented as the value of the attribute.
     * @return Attribute
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the value of the attribute.
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

