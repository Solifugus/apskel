<?php

# File: ~/framework_1.0/controller.php
# Purpose: base class for all controllers

# The Base Class for All Controllers 
class Controllers
{
  protected $models;                // instance of the models class like-named with the controller class
  protected $views;                 // instance of the views class like-named with the controller class

  protected $framework;                // systems settings and basic functions
	public function __construct() {
	}

} // End of Controller Class


