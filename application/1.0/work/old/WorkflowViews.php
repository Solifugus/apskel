<?php

# File: ~/1.0/workflow/WorkflowViews.php
# Purpose: to provide a business process work management facility views

require_once('Views.php');

# Template for Construction of a Controller 
class WorkflowViews extends Views
{
  # Constructor
  public function __construct()
  {
    $this->loadViewTemplates(dirname(__FILE__));
  } 

  public function getTasks($param_tasks, $param_format = 'html')
  {
    switch($param_format)
    {
      case 'html':
        $view = $this->getTasksAsHtml($param_tasks);
        break;

      case 'json':
        $view = $this->getTasksAsJson($param_tasks);
        break;

      case 'xml':
        $view = $this->getTasksAsXml($param_tasks);
        break;

      default:
        // TODO: deal with error
        break;
    }
    return $view;
  }

  private function getTasksAsHtml($param_tasks)
  {
    $task_listing = '';
    foreach($param_tasks as $task)
    {
      $id           = $task['id'];
      $title        = addslashes($task['title']);
      $percent_left = $task['percent_left'];
      $due          = addslashes($task['due_date']);
      if($percent_left >= 50) $status = 'task_status_green';   // time is sufficient
      if($percent_left <= 25) $status = 'task_status_yellow';  // time is short
      if($percent_left <= 10) $status = 'task_status_orange';  // time is very short
      if($percent_left <= 0)  $status = 'task_status_red';     // overdue
      $task_listing .= "\t<tr class=\"$status\"><td>$id</td><td>$title</td><td>$due</td></tr>\n";
    }
    $view = <<<EndOfHTML

<html>
<head>
  <style type="text/css">
    #tasks_view         { background-color: grey; }
    #title_row          { background-color: black; color: white; font-weight: bolder; }
    #headings_row       { font-weight: bolder; }
    #refresh_counter    { text-align: center; }
    #links_row          { text-align: center; }
    .task_status_green  { background-color: green; }
    .task_status_yellow { background-color: yellow; }
    .task_status_orange { background-color: orange; }
    .task_status_red    { background-color: red; }
  </style>

  <script type="text/javascript">
    refresh_count = undefined;

    function begin()
    {
      refresh_count = 15;
      setInterval("refresh();",1000);
    }

    function refresh()
    {
      refresh_count = refresh_count - 1;
      if(refresh_count < 0) 
      {
        // TODO: AJAX call to refresh data..
        refresh_count = 15;
      }
      document.getElementById('refresh_counter').textContent = refresh_count;
    }
  </script>
</head>
<body onLoad="begin();">

<table id="tasks_view" align="center" cellspacing="0" cellpadding="3">
<tr id="title_row"><td colspan="2">Pending Tasks</td><td id="refresh_counter">X</td>
<tr id="headings_row"><td>ID</td><td>Title</td><td>Due</td></tr>
$task_listing
<tr id="links_row"><td colspan="3"><a href="workflow/workflows">Workflows</a> - <a href="workflow/reports">Reports</a></td></tr>
</table>

</body>
</html>

EndOfHTML;
    return $view;
  }

  private function getTasksAsJson($param_tasks)
  {
    $view = '[ ';
    foreach($param_tasks as $task)
    {
      $id           = $task['id'];
      $title        = addslashes($task['title']);
      $percent_left = $task['percent_left'];
      $due          = addslashes($task['due_date']);
      $view .= "{ id: $id, title: '$title', percent_left: $percent_left, due: '$due' }, ";
    }
    $view = rtrim($view,', ');
    $view .= ' ]';
    return $view;
  }

  private function getTasksAsXml($param_tasks)
  {
    $view = "<tasks>\n";
    foreach($param_tasks as $task)
    {
      $id           = $task['id'];
      $title        = addslashes($task['title']);
      $percent_left = $task['percent_left'];
      $due          = addslashes($task['due_date']);
      $view .= "\t<task id=\"$id\" title=\"$title\" percent_left=\"$percent_left\" due=\"$due\"</task>\n";
    }
    $view .= "</task>\n";
    return $view;
  }

  public function getDetail()
  {
    print "Details View";
  }

  public function getWorkflows()
  {
    print "Workflow View";
  }

  public function getReports()
  {
    print "Report Page";
  }

  public function getSQL()
  {
    // TODO: get SQL from file..
  }

}

