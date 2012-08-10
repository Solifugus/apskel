<?php

# File: ~/1.0/workflow/WorkflowController.php
# Purpose: to provide a business process work management facility 

require_once('Controller.php');
require_once('workflow/WorkflowModels.php');
require_once('workflow/WorkflowViews.php');

# Template for Construction of a Controller 
class WorkflowController extends Controller
{
  # Constructor
  public function __construct($param_system)
  {
    // Record System Settings & Request Interpretation Results
    $this->framework            = $param_system;
    $this->controller_directory = dirname(__FILE__);

    // Instantiate the associated model and view
    $this->models = new WorkflowDataModels($param_system);
    $this->views  = new WorkflowViews($param_system);
  } // end of __construct

  # Handle Default Request
  public function processDefault()
  {
    print "The default request of the default controller was called.";
  }

  public function processTasks()
  {
    // TODO: derive this data from the workflow data model 
    print $this->views->getTasks($this->models->getUserTasks());
  }

  public function processDetail()
  {
    print "Details View";
  }

  public function processWorkflows()
  {
    print "Workflow View";
  }

  public function processReports()
  {
    print "Report Page";
  }

}

