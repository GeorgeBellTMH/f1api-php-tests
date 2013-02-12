<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Giving Realm.
  * @class FellowshipOneEventsTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
  * @copyright 2013 Tracy Mazelin
  * @author Tracy Mazelin tracy.mazelin@activenetwork.com
  * @requires PHP 5.4, PECL OAuth, PHPUnit, XDebug (for logs)
  *
  */

require_once('../lib/FellowshipOne.php');
require_once('../lib/settings.php');

class FellowshipOneGivingTest extends PHPUnit_Framework_TestCase
{
    protected static $f1;
    protected static $today;
    
    public static function setupBeforeClass()
    {
        global $settings;
        $env = 'staging';
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']);        
    }

    // Households

    // Households: Search (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd)
    // Household Member Types: List (3rd) | Show (3rd)
    // People

    // People: Search (3rd) | List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd)
    // Household Member Types: List (3rd) | Show (3rd)
    // People Attributes: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd) | Delete (3rd)
    // People Images: Show (3rd) | Create (3rd) | Update (3rd)
    // Addresses

    // Addresses: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd) | Delete (3rd)
    // Address Types: List (3rd) | Show (3rd)
    // Attributes

    // Attribute Groups: List (3rd) | Show (3rd)
    // Attributes: List (3rd) | Show (3rd)
    // Communications

    // Communications: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd) | Delete (3rd)
    // Communication Types: List (3rd) | Show (3rd)
    // Denominations

    // Denominations: List (3rd) | Show (3rd)
    // Occupations

    // Occupations: List (3rd) | Show (3rd)
    // Schools

    // Schools: List (3rd) | Show (3rd)
    // Statuses

    // Statuses: List (3rd) | Show (3rd)
    // Sub Statuses: List (3rd) | Show (3rd)
    // Requirements
    // Requirements

    // Requirements: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd)
    // Requirement Statuses

    // Requirement Statuses: List (3rd) | Show (3rd)
    // Background Check Statuses

    // Background Check Statuses: List (3rd) | Show (3rd)
    // People Requirements

    // People Requirements: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd)
    // Requirement Documents

    // Requirement Documents: Show (3rd) | Create (3rd) | Update (3rd)
   

}