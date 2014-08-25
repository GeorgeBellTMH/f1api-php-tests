<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Events Realm.
  * @class FellowshipOneEventsTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
  * @copyright 2013 Tracy Mazelin
  * @author Tracy Mazelin tracy.mazelin@activenetwork.com
  * @requires PHP 5.4, PECL OAuth, PHPUnit, XDebug (for logs)
  *
  */

require_once('../lib/FellowshipOne.php');
require_once('../lib/settings.php');

class FellowshipOneEventsV2Test extends PHPUnit_Framework_TestCase
{
    protected static $f1;
    protected static $today;
    
    public static function setupBeforeClass()
    {
        global $settings;
        $env = 'int';
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']);        
    }
   
    // MINISTRIES START

     /**
     * @group Ministries
     */
    public function testMinistryList()
    {
      $r = self::$f1->get('/events/v2/ministries?pagesize=5');
      $ministryId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($ministryId, "No ministry id returned");
      return $ministryId; 
    }

    /**
     * @group Ministries
     * @depends testMinistryList
     */
    public function testMinistryShow($ministryId)
    {
     $r = self::$f1->get("/events/v2/ministries/{$ministryId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // ACTIVITIES START
    /**
     * @group Activities
     */
    public function testActivityList()
    {
      $r = self::$f1->get('/events/v2/activities?pagesize=5');
      $activityId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($activityId, "No activity id returned");
      return $activityId; 
    }

    /**
     * @group Activities
     * @depends testActivityList
     */
    public function testActivityShow($activityId)
    {
     $r = self::$f1->get("/events/v2/activities/{$activityId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // ASSIGNMENTS START

    // SCHEDULES START

     /**
     * @group Schedules
     * @depends testActivityList
     */
    public function testScheduleList($activityId)
    {
      $r = self::$f1->get("/events/v2/activities/{$activityId}/schedules?pagesize=5");
      
      $scheduleId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($scheduleId, "No activity id returned");
      return $scheduleId; 
    }

    /**
     * @group Schedules
     * @depends testActivityList
     * @depends testScheduleList
     */
    public function testScheduleShow($activityId, $scheduleId)
    {
     $r = self::$f1->get("/events/v2/activities/{$activityId}/schedules/{$scheduleId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }



}
?>