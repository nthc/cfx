<?php
/**
 * A simple container for form elements. This container does
 * not expose itself to styling by default as such custom styling can be added
 * through CSS classes. You could use this controller to render form elements
 * in special cases where you do not want them to appear in form tags when
 * rendered.
 * 
 * @ingroup Forms
 */
class BoxContainer extends Container
{
    public function __construct()
    {
        parent::__construct();
    }

    public function render()
    {
        $ret = "";
        $this->addAttribute("class","fapi-box {$this->getCSSClasses()}");
        $ret .= "<div {$this->getAttributes()}>";
        $ret .= $this->renderElements();
        $ret .= "</div>";
        return $ret;
    }

}
?>
