<?php
//! The Tab is a special container for containing elements. The tab can
//! only be contained within the TabLayout container. Tabs are used to
//! present notebook style interfaces for orgarnizing multiple forms.
//! \ingroup Forms

class Tab extends Container
{
    protected $legend;
    protected $selected;

    public function __construct($legend="")
    {
        parent::__construct();
        $this->legend = $legend;
    }

    //! Gets the legend displayed at the top of the Tab.
    public function getLegend()
    {
        return $this->legend;
    }

    //! Sets the legend displaued at the top of the Tab.
    public function setLegend($legend)
    {
        $this->legend = $legend;
    }

    public function render()
    {
        $this->addAttribute("class","fapi-tab {$this->getCSSClasses()}");
        $ret = "<div {$this->getAttributes()}>";
        $ret .= $this->renderElements();
        $ret .= "</div>";
        return $ret;
    }

    //! Returns whether this Tab is selected.
    public function getSelected()
    {
        return $selected;
    }

    //! Sets the selected status of the Tab.
    public function setSelected($selected)
    {
        $this->selected = $selected;
    }
}
?>
