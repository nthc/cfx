<?php
class DefaultRenderer
{
    public static function render($element)
    {
        if($element->getType()=="Field")
        {
            print "<div class='fapi-element-div'>";
            print "<div class='fapi-label'>".$element->getLabel();
            if($element->getRequired())
            {    
                print "<span class='fapi-required'>*</span>";
            }
            print "</div>";
            if($element->hasError())
            {
                print "<div class='error'>";
                print "<ul>";
                foreach($element->getErrors() as $error)
                {
                    print "<li>$error</li>";
                }
                print "</ul>";
                print "</div>";
            }
        }
        
        $element->render();
        
        if($element->getType()!="Container")
        {
            print "<div class='fapi-description'>".$element->getDescription()."</div>";
        }
        if($element->getType()=="Field")
        {
            print "</div>";
        }        
    }
}
?>