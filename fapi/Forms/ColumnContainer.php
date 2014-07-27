<?php
/**
 * A special container which lays out its form elements in columns. The elements
 * are evenly packed into the columns. The ColumnContainer class is an extension
 * of the TableLayout class with has one but different columns.
 *
 * @author jainooson@gmail.com
 * @ingroup Forms
 *
 */
class ColumnContainer extends TableLayout
{
    /**
     * A boolean flag which is set whenever the contents of the table have been
     * rearranged. Rearranging is normally done right when the container is being
     * rendered.
     */
    protected $reArranged;

    public function __construct($num_columns=1)
    {
        parent::__construct(1,$num_columns);
    }
    
    public function add($element)
    {
        foreach(func_get_args() as $element)
        {
            parent::add($element);
        }
        return $this;
    }

    public function render()
    {
        //print count($this->elements)."<br/>";
        $num_elements = count($this->elements);
        if(!$this->reArranged)
        {
            $elements_per_col = ceil(count($this->elements)/$this->num_columns);
            for($j = 0, $k=0; $j < $this->num_columns; $j++)
            {
                for($i=0;$i<$elements_per_col;$i++,$k++)
                {

                    if($k<$num_elements)
                    {
                        $this->elements[$k]->parent = null;
                        parent::add($this->elements[$k],0,$j);
                    }
                    else
                    {
                        break;
                    }
                }
            }
            $this->reArranged = true;
        }

        return parent::render();
    }
}

