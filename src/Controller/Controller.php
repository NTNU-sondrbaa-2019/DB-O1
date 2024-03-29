<?php
/** The Controller of the IMT2571 Assignment #1 MVC-example.
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
// Needed to overcome a Chrome bug when testing for html type input
header('X-XSS-Protection: 0');

// require_once('Model/SessionEventModel.php');
require_once('Model/DBEventModel.php');
require_once('Model/Event.php');
require_once('View/EventArchiveView.php');
require_once('View/EventView.php');
require_once('View/ErrorView.php');

/** The Controller is responsible for handling user requests, for exchanging data with the Model,
 * and for passing user response data to the various Views.
 * @see Model The Model class holding Event data.
 * @see EventView The View class displaying information about one Event.
 * @see EventArchiveView The View class displaying information about all Events.
 * @see ErrorView The View class displaying information about errors encountered when processing the request.
 */
class Controller
{
    public $model;

    /**
     * Query string key passed in HTTP for identifying the requested operation.
     */
    const OP_PARAM_NAME = 'op';

    /**
     * Query string value passed in operation for delete operations.
     * @see OP_PARAM_NAME
     */
    const DEL_OP_NAME = 'del';

    /**
     * Query string value passed in operation for insert operations.
     * @see OP_PARAM_NAME
     */
    const ADD_OP_NAME = 'add';

    /**
     * Query string value passed in operation for modification operations.
     * @see OP_PARAM_NAME
     */
    const MOD_OP_NAME = 'mod';

    public function __construct()
    {
        session_start();
//        $this->model = new SessionEventModel();
        $this->model = new DBEventModel();
    }

    /** The one function running the controller code. It parses function requests
      * encoded in HTTP, calls the Model class to process the function, and
      * passes the results to the proper View class for formatting the response.
      */
    public function invoke()
    {
        try {
            if (isset($_GET['id'])) {
                // A specific event is selected - show the requested event
                $Event = $this->model->getEventById($_GET['id']);
                if ($Event) {
                    $view = new EventView(
                        $Event,
                        self::OP_PARAM_NAME,
                        self::DEL_OP_NAME,
                        self::MOD_OP_NAME
                    );
                    $view->create();
                } else {
                    $view = new ErrorView();
                    $view->create();
                }
            } else {
                // Variable used to pass newly added events to the EventArchiveView
                $newEvent = null;
                //A Event record is to be added, deleted, or modified
                if (isset($_POST[self::OP_PARAM_NAME])) {
                    switch ($_POST[self::OP_PARAM_NAME]) {
                    case self::ADD_OP_NAME:
                        $newEvent = new Event(
                            $_POST['title'],
                            $_POST['date'],
                            $_POST['description']
                        );
                        $this->model->addEvent($newEvent);
                        break;
                    case self::DEL_OP_NAME:
                        $this->model->deleteEvent($_POST['id']);
                        break;
                    case self::MOD_OP_NAME:
                        $Event = new Event(
                            $_POST['title'],
                            $_POST['date'],
                            $_POST['description'],
                            $_POST['id']
                        );
                        $this->model->modifyEvent($Event);
                        break;
                    }
                }
                // No special Event is requested, we'll show a list of all Events
                $Events = $this->model->getEventArchive();
                $view = new EventArchiveView(
                    $Events,
                    self::OP_PARAM_NAME,
                    self::ADD_OP_NAME,
                    $newEvent
                );
                $view->create();
            }
        } catch (InvalidArgumentException $e) {
            // User entered invalid data
            $view = new ErrorView("Invalid data received: {$e->getMessage()}");
            $view->create();
        } catch (PDOException $e) {
            // Database operation failed
            $view = new ErrorView('Database operation failed - please try again later');
            $view->create();
        } catch (Exception $e) {
            // Something else failed
            $view = new ErrorView();
            $view->create();
        }
    }
}
