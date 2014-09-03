<?php
/**
 * The default renderer.
 */


/**
 * The default renderer head function
 */
function default_renderer_head()
{

}

/**
 * The default renderer body function
 *
 * @param $element The element to be rendererd.
 */
function default_renderer_element($element, $showfields=true)
{
    $ret = "";
    if($element->getType()=="HiddenField")
    {
        return $element->render();
    }
    $attributes = $element->getAttributes(Element::SCOPE_WRAPPER);
    $ret .= "<div $attributes class='fapi-element-div' ".($element->getId()==""?"":"id='".$element->getId()."_wrapper'").">";

    if($element->getType()=="Field" && $element->getLabel()!="")
    {
        $ret .= "<div class='fapi-label'>".$element->getLabel();
        if($element->getRequired() && $element->getLabel()!="" && $element->getShowField())
        {
            $ret .= "<span class='fapi-required'>*</span>";
        }
        $ret .= "</div>";
    }

    $ret .= "<div class='fapi-message' id='".$element->getId()."-fapi-message'></div>";

    if($element->hasError())
    {
        $ret .= "<div class='fapi-error'>";
        $ret .= "<ul>";
        foreach($element->getErrors() as $error)
        {
            $ret .= "<li>$error</li>";
        }
        $ret .= "</ul>";
        $ret .= "</div>";
    }

    if($element->getType()=="Field")
    {
        if($element->getShowField())
        {
            $ret .= $element->render();
        }
        else
        {
            $ret .= $element->getDisplayValue();
            $ret .= "<input type='hidden' name='".$element->getName()."' value='".$element->getValue()."'/>";
        }
    }
    else if($element->getType() == "RadioButton")
    {
        if($element->getShowField())
        {
            $ret .= $element->render() . "<span class='fapi-label'>" . $element->getLabel() . "</span>";
        }
    }
    else
    {
        $ret .= $element->render();
    }

    if($element->getType()!="Container" && $element->getShowField())
    {
        $ret .= "<div ".($element->getId()==""?"":"id='".$element->getId()."_desc'")." class='fapi-description'>".$element->getDescription()."</div>";
    }
    $ret .= "</div>";

    return $ret;
}

/**
 * The foot of the default renderer.
 *
 */
function default_renderer_foot()
{

}

?>
