<?php

# File: ~/application_1.0/workflow/workflow.model.php
# Purpose: to provide a business process work management facility views

require_once('Models.php');

# Template for Construction of a Controller 
class WorkflowDataModels extends Models
{
  // Constructor
  public function __construct($param_system)
  {
    $this->framework = $param_system;
  } 

  // Get Array of User's Active Tasks
  function getUserTasks($param_user = null)
  {
    // Select user ID to use
    if($param_user !== null) $user_id = $param_user;
    //else $user_id = $this->system->getUserId();

    // Build & run query
    $sql  = "SELECT t.id, t.title, t.hours_estimated - t.hours_spent AS hours_left, t.due_date ";
    $sql .= "FROM tasks t LEFT JOIN task_assignees a ON t.id = a.task_id ";
    $sql .= "WHERE user_id = $user_id ";

    // Collect and return array of:  id, title, percent_left, due_date
    // TODO: working..
    $tasks = array(
                    array( 'id'=>35, 'title'=>'task number five',  'percent_left'=>0,  'due_date'=>'2011-12-01' ),
                    array( 'id'=>35, 'title'=>'task number four',  'percent_left'=>9,  'due_date'=>'2011-12-01' ),
                    array( 'id'=>34, 'title'=>'task number three', 'percent_left'=>19, 'due_date'=>'2011-12-01' ),
                    array( 'id'=>33, 'title'=>'task number two',   'percent_left'=>49, 'due_date'=>'2011-12-01' ),
                    array( 'id'=>32, 'title'=>'task number one',   'percent_left'=>51, 'due_date'=>'2011-12-01' )
                  );
    return $tasks; 

  }

  // Get a Task's Details
  function getTaskDetails($param_id)
  {
    // TODO: return id, title, description, start_date, due_date, completed_on, hours_estimated, notes

    // Collect task level fields
    $sql = "SELECT id, title, description, start_date, due_date, commpleted_on, hours_estimated, notes ";
    $sql .= "FROM tasks ";
    $sql .= "WHERE id = $param_id";
    // TODO: working..

    // Collect assignee fields-- assignees: id, user_id, user_name, email, hours_spent
    $sql = "SELECT id, user_id, hours_spent, is_approver ";
    $sql .= "FROM tasks ";
    $sql .= "WHERE task_id = $param_id";
    // TODO: working (use rows where approver is false)

    // Collect approver fields-- approvers: id, user_id, uesr_name, email, hours spent
    // TODO: working (use reows where approver is true)

    // Collect prerequisite fields--prerequisites: id, title, due_date
    $sql = "SELECT p.task_id, t.title, t.due_date ";
    $sql .= "FROM task_prerequisites p LEFT JOIN tasks t ON p.task_id = t.id ";
    $sql .= "WHERE p.task_id = $param_id";
    // TODO: working..

    // Collect resources fields--resources: id, type, reference
    $sql = "SELECT id, resource, reference ";
    $sql .= "FROM task_resources ";
    $sql .= "WHERE task_id = $param_id";
    // TODO: working.. 

    // Collect skills fields--skills: id, skill
    $sql = "SELECT id, skill ";
    $sql .= "FROM task_skills ";
    $sql .= "WHERE task_id = $param_id";
    // TODO: working.. 

    // Collect events fields--events: id, when_happened, user_id, user_name, event
    $sql = "SELECT id, post_date, user_id, event ";
    $sql .= "FROM task_events ";
    $sql .= "WHERE task_id = $param_id";
    // TODO: working.. 
    $details = array('id'=>241, 'title'=>'Do something', 'description'=>'A task in which you must do something.', 'start_date'=>'2011-10-01', 'due_date'=>'2011-12-15', 'completed'=>null, 'hours_estimated'=>3.5, 'notes'=>'nothing to add',
                 'assignees'=>array(array('id'=>22,'user_id'=>33, 'user_name'=>'worker one','email'=>'workerone@workmosaic.com','hours_spent'=>1.25),array('id'=>24,'name'=>'Worker Two','email'=>'workertwo@workmosaic.com','hours_spent'=>.75)),
                 'approvers'=>array(array('id'=>18,'user_id'=>32, 'user_name'=>'manager one','email'=>'managerone@workmosaic.com','hours_spent'=>.25),array('id'=>12,'name'=>'Manager Two','email'=>'managertwo@workmosaic.com','hours_spent'=>.1)),
                 'prerequisites'=>array(array('id'=>233,'title'=>'Do whatever','due_date'=>'2011-11-05'),array('id'=>230,'title'=>'Do stuff','due_date'=>'2011-11-06')),
                 'resources'=>array(array('id'=>14,'type'=>'file','reference'=>'C:\somefile.docx'),array('id'=>13,'type'=>'url','reference'=>'http://somewhere.com')),
                 'skills'=>array(array('id'=>3, 'skill'=>'typing'),array('id'=>4, 'skill'=>'thinking')),
                 'events'=>array(array('id'=>2031,'when_happened'=>'2011-10-01', 'user_id'=>45, 'user_name'=>'Joe Willis', 'event'=>'Looked around..'),array('id'=>3066,'when_happened'=>'2011-10-02', 'user_id'=>50, 'user_name'=>'Barny Turfwar', 'event'=>'Left my mark.'))
               ); 
    return $details;
  }

  // Get a Listing of Workflows
  function getWorkflowListing($param_started, $param_not_started, $param_completed, $param_templates)
  {
    // TODO: return id, title
  }

  // Get a Workflow's Details
  function getWorkflowDetails($param_id)
  {
    // TODO: id, title, template, activated
    //       tasks: id, title, hours_estimated, hours_spent, start_date, due_date, hardlinked
  }
} // End of Class
