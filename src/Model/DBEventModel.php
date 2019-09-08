<?php

/** The Model implementation of the IMT2571 Assignment #1 MVC-example, storing
  * data in a MySQL database using PDO.
  * @author Rune Hjelsvold
  * @see http://php-html.net/tutorials/model-view-controller-in-php/
  *      The tutorial code used as basis.
  */

require_once("AbstractEventModel.php");
require_once("Event.php");
require_once("dbParam.php");

/** The Model is the class holding data about a archive of events.
  */
class DBEventModel extends AbstractEventModel
{
    protected $db = null;

    /**
      * @param PDO $db PDO object for the database; a new one will be created if
      *                no PDO object is passed
      * @throws PDOException
      */
    public function __construct($db = null)
    {
        if ($db) $this->db = $db;
        else $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PWD);
    }

    /** Function returning the complete list of events in the archive. Events
      * are returned in order of id.
      * @return Event[] An array of event objects indexed and ordered by id.
      * @throws PDOException
      */
    public function getEventArchive()
    {
        $eventList = array();

        $sth = $this->db->prepare("SELECT * FROM event");
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) $eventList[] = new Event($row["title"], $row["date"], $row["description"], $row["id"]);

        return $eventList;
    }

    /** Function retrieving information about a given event in the archive.
      * @param integer $id the id of the event to be retrieved
      * @return Event|null The event matching the $id exists in the archive;
      *         null otherwise.
      * @throws PDOException
      */
    public function getEventById($id)
    {
        $event = null;

        Event::verifyId($id);

        $sth = $this->db->prepare("SELECT * FROM event WHERE id = ?");
        $sth->execute([$id]);
        $row = $sth->fetch(PDO::FETCH_ASSOC);

        if ($row) $event = new Event($row["title"], $row["date"], $row["description"], $row["id"]);

        return $event;
    }

    /** Adds a new event to the archive.
      * @param Event $event The event to be added - the id of the event will be set after successful insertion.
      * @throws PDOException
      * @throws InvalidArgumentException If event data is invalid
      */
    public function addEvent($event)
    {
        $event->verify(true);
        $sth = $this->db->prepare("INSERT INTO event (title, date, description) VALUES (?, ?, ?)");
        $sth->execute([$event->title, $event->date, $event->description]);
        $event->id = $this->db->lastInsertId();
    }

    /** Modifies data related to a event in the archive.
      * @param Event $event The event data to be kept.
      * @throws PDOException
      * @throws InvalidArgumentException If event data is invalid
     */
    public function modifyEvent($event)
    {
        $event->verify();
        $sth = $this->db->prepare("UPDATE event SET title = ?, date = ?, description = ? WHERE id = ?");
        $sth->execute([$event->title, $event->date, $event->description, $event->id]);
    }

    /** Deletes data related to a event from the archive.
      * @param $id integer The id of the event that should be removed from the archive.
      * @throws PDOException
     */
    public function deleteEvent($id)
    {
        Event::verifyId($id);
        $sth = $this->db->prepare("DELETE FROM event WHERE id = ?");
        $sth->execute([$id]);
    }
}
