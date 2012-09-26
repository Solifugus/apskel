<?php

# File: ~/framework_1.0/view.php
# Purpose: base class for all coded views 

# The Base Class for All Coded Views
class Views
{
  protected $templates;               // view templates (associative array loaded from *.view.* files)
  
  public function __construct() {
  }

  // Extract Controller's Views From Files Into Associative Array
  public function loadViewTemplates($param_view_directory)
  {
    $this->templates = array();

    // For each file in controller directory with ".view." in its name..
    if($open_directory = opendir($param_view_directory))
    {
      while (($file_name = readdir($open_directory)) !== false)
      {
        if(strpos($file_name,'.view.'))
        {
          // For each record in the file
          $open_file = fopen("{$param_view_directory}/$file_name","r"); // TODO: trap error
          $current_view = '';
          while(!feof($open_file))
          {
            $line = rtrim(fgets($open_file));

            // If "~view_name:" pattern, Mark new current view
            if(preg_match('/~([A-Za-z0-9_ ]+):/',$line,$match) > 0)  // TODO: make this only work if line starts with it..
            {
              $current_view = trim($match[0],' ~:');
              $this->templates[$current_view] = '';
              continue;
            }

            // If in a view, add line to current view
            if($current_view != '') { $this->templates[$current_view] .= $line; }
          }
        }
      }
      closedir($open_directory);
    } // TODO: add else condition, if failed to open directory..
  }  // End of loadViews() Function

  public function getViewFromTemplate($param_template, $param_data = array())
  {
    // TODO: load template with array data and return it.
  }

} // End of View Class
