<?php
global $table_renderer_hidden_fields;

function table_renderer_head()
{
    return "<table class='fapi-layout-table'>";
}

function table_renderer_element($element, $showfields=true)
{
    global $table_renderer_hidden_fields;
    
    if($element->getType()=="HiddenField")
    {
        $table_renderer_hidden_fields .= $element->render();
        return;
    }
    else
    {
    	$ret = "<tr ".($element->getId()==""?"":"id='".$element->getId()."_wrapper'")." ".$element->getAttributes(Element::SCOPE_WRAPPER).">";
    }
    //$ret .= "<div class='fapi-element-div' ".($element->getId()==""?"":"id='".$element->getId()."_div'").">";

    if($element->getType()=="Field")
    {
        $ret .= "<td class='fapi-layout-table-label'><div class='fapi-label'>".$element->getLabel();
        if($element->getRequired() && $element->getLabel()!="" && $element->getShowField())
        {
            $ret .= "<span class='fapi-required'>*</span>";
        }
        $ret .= "</div></td>";        
    }
    else if($element->hasLabel() === true)
    {
    	$ret .= "<td class='fapi-layout-table-label'><div class='fapi-label'>".$element->getLabel()."</div></td>";
    }
    
    
    $ret .="<td class='fapi-layout-table-field'>";
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
    else
    {
        $ret .= $element->render();
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

    if($element->getType()!="Container" && $element->getShowField())
    {
        $ret .= "<div class='fapi-description'>".$element->getDescription()."</div>";
    }
    //$ret .= "</div>";

    $ret .= "</td></tr>";

    return $ret;
}

function table_renderer_foot()
{   global $table_renderer_hidden_fields;
    $hiddenFields = $table_renderer_hidden_fields;
    $table_renderer_hidden_fields="";
    return "</table>$hiddenFields";
}

