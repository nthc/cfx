<?php
/**
 * A simple container for containing form elements. This container does
 * not expose itself to styling by default but styling can be added
 * by adding a css class through the attributes interface.
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
