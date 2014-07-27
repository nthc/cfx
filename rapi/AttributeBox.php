<?php
/**
 * Renders a two column table which has headings on the left column and the body
 * text on the right column in reports. The heading text on the left is usually
 * displayed in bold type for emphasis and the regular text is displayed in 
 * normal type. This reporting item is useful for dispaying information where 
 * a particular fixed label is assigned to some variable text.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AttributeBox extends ReportContent
{
    /**
     * The data to be displayed on the report
     * @var array
     */
    public $data;
    
    /**
     * Styling information for the AttributeBox. This reporting item takes
     * two style attributes purposefully for describing its font. The first
     * is font which describes the type of font (e.g. Arial). The second is
     * size which describes the size of the font (e.g. 12).
     * @var array
     */
    public $style;
    
    /**
     * Create a new AttributeBox.
     * @param array $data The data for the AttributeBox
     * @param array $style The styling description for the AttributeBox
     */
    public function __construct($data = null, $style = null)
    {
        $this->data = $data;
        $this->style = $style;
    }
    
    public function getType()
    {
        return "attributes";
    }
}