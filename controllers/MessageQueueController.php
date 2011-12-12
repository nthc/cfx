<?php
/**
 * A controller for handling operations over the message queue. The message 
 * queue provides a mechanism through which resource intensive operations
 * which are likely to time out on the browser are executed. By detaching these
 * operations and running them in the background, this coltroller provides a 
 * medium through which the defered operation's progres could be monitored. This
 * controller relies on the MessageQueue class to provide the infrastracture used
 * in executing the background operations.
 * 
 * This abstract class has four methods which need to be extended.
 * 
 * @ingroup Controllers
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
abstract class MessageQueueController extends Controller
{
    /**
     * The ID associated with the running job.
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     * @param unknown_type $name
     */
    public function __construct($name)
    {
        /*
         * Generate an ID for the operation. This ID would always be unique yet
         * it would always remain the same for every individual user.
         */
        $this->id = sha1($_SESSION["user_id"] . $name);
        $this->_showInMenu = true;
    }

    /**
     * Clears the data for the job. If this action is not performed, the data
     * still remains in the system and the controller would always display the
     * results of the last run of the job.
     */
    public function clear()
    {
        MessageQueue::delete($this->id);
        $this->clearJobResults();
        Application::redirect($this->path);
    }

    /**
     * Draws a progress notifier when the job is still in session.
     */
    public function loadingBox()
    {
        return 
        "<div style='text-align:center; width:50%; margin:50px;padding:50px;background-color:#ffffff'>
            <img src='/images/ajax-loader-round.gif' />
            <br/>
            <b>Processing</b>
            <br/>
            Your job is still processing
        </div>
        <script type='text/javascript'>
            setTimeout('document.location = document.location', 10000);
        </script>";
    }

    /**
     * @warning Dont override this method for the MessageQueueController. 
     * (unless you really know what you are doing) This is the part
     * where all the logic for properly routing the actions takes place.
     * @see lib/controllers/Controller::getContents()
     */
    public function getContents()
    {
        $status = MessageQueue::getStatus($this->id);
        switch($status)
        {
            case "NEW":
            case "EXECUTING":
                return $this->loadingBox();
            case "COMPLETE":
                return $this->jobResults();
            default:
                $form = new Form();
                $form->add($this->jobSettings());
                $form->setSubmitValue("Execute");
                $form->setCallback($this->getClassName()."::routeExecuteJob", $this);
                return $form->render();

        }
    }

    /**
     * Utility static method for routing job execution. This method is a callback
     * method for the form which captures the job settings. All this method
     * does is to route the 
     * @param string $data
     * @param string $form
     * @param string $instance
     */
    public static function routeExecuteJob($data, &$form, $instance)
    {
        $instance->executeJob($data);
        Application::redirect($instance->path);
    }

    /**
     * This method returns a Container object to be put unto the job settings form.
     * The job settings form contains the parameters which are to be used for
     * the running of the Job.
     * @return Container
     */
    abstract protected function jobSettings();

    /**
     * This method displays the results of the job.
     */
    abstract protected function jobResults();

    /**
     * This method executes the JOB.
     * @param array $data
     */
    abstract protected function executeJob($data);

    /**
     * This method clears the job results.
     */
    abstract protected function clearJobResults();

    /**
     * (non-PHPdoc)
     * @see lib/controllers/Controller::getPermissions()
     */
    public function getPermissions()
    {
        return array(
            array("name"=>"{$this->name}_can_execute", "label"=>"Can Execute")
        );
    }
}
