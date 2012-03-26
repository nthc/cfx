<?php
class SystemRemindersWidget extends Widget
{
    public $label = "System Reminders";
    private static $reminders = array();
    
    public static function addReminder($reminder)
    {
        if(is_string($reminder))
        {
            $reminder = array('reminder' => $reminder);
        }
        SystemRemindersWidget::$reminders[] = $reminder;
    }
    
    public function render()
    {
    	if(count(SystemRemindersWidget::$reminders) > 0)
    	{
            //$reminders = "<ul><li>" . implode("</li><li>", SystemRemindersWidget::$reminders) . "</li></ul>";
            $reminders = "<ul>";
            foreach(self::$reminders as $reminder)
            {
                $colour = $reminder['colour'] == '' ? '#e0e0e0' : $reminder['colour'];
                
                if(isset($reminder['timestamp']))
                {
                    $time = ucfirst(Common::sentenceTime($reminder['timestamp'], array('elaborate_with'=>'ago')));
                    $time .= " - " . date("jS F, Y g:i A", $reminder['timestamp']);
                    $time = "<div style='color:#707070; margin-bottom:10px'>$time</div>";
                }
                else
                {
                    $time = '';
                }
                
                $reminders .= "<li style='border-left:10px solid $colour; border-bottom:1px solid $colour; margin-bottom:1px'> {$time} {$reminder['reminder']}</li>";
            }
            $reminders .= "</ul>";
            $numReminders = count(SystemRemindersWidget::$reminders);
            if($numReminders > 1) $plural = 's';
            return "<div id='reminders-widget'>
                    <h3> You have $numReminders reminder$plural</h3>
                    <div id='reminders-list'>".$reminders."</div>
                </div>";
    	}
    	else
    	{
    		return false;
    	}        
    }
}
