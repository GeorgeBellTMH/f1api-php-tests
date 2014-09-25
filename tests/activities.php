<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Events Realm.
  * @class FellowshipOneEventsV2Test
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
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
        $env = 'staging';
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

    /**
     * @group Assignments
     * @depends testActivityList
     */
    public function testAssignmentList($activityId)
    {
      $r = self::$f1->get("/events/v2/activities/{$activityId}/assignments");
      $assignmentId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($assignmentId, "No Assignment ID");
      return $assignmentId;
    }

    /**
     * @group Assignments
     * @depends testActivityList
     * @depends testAssignmentList
     */
    public function testAssignmentShow($activityId, $assignmentId)
    {
      $r = self::$f1->get("/events/v2/activities/{$activityId}/assignments/{$assignmentId}");
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    } 

   

   /**
     * @group Assignments
     */
    public function testAssignmentNew()
    {
      $model = self::$f1->get("/events/v2/activities/assignments/new");
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }

    /**
     * @group Assignments
     * @depends testActivityList
     * @depends testAssignmentNew
     */
    public function testAssignmentCreate($activityId, $model)
    {
      $model['type'] = "Participant";
      $model['person']['id'] = "123";
      $model['activity']['id'] = $activityId;
      
      $r = self::$f1->post($model, "/events/v2/activities/{$activityId}/assignments");
      $assignment = $r['body'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($assignment, "No Response Body");
      return $assignment;

    }

    /**
     * @group Assignments
     * @depends testActivityList
     * @depends testAssignmentCreate
     */
    public function testAssignmentUpdate($activityId, $assignment)
    {
      $assignmentId = $assignment['id'];
      $assignment['person']['id'] = "1234";
      $r = self::$f1->put($assignment, "/events/v2/activities/{$activityId}/assignments/{$assignmentId}");
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

    /**
     * @group Assignments
     * @depends testActivityList
     * @depends testAssignmentCreate
     */
    public function testAssignmentDelete($activityId, $assignment)
    {
      $assignmentId = $assignment['id'];
      $r = self::$f1->delete("/events/v2/activities/{$activityId}/assignments/{$assignmentId}"); 
      $this->assertEquals('204', $r['http_code']);   
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }

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
      $this->assertNotEmpty($scheduleId, "No schedule id returned");
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

    // INSTANCES START

    /**
     * @group Instances
     * @depends testActivityList
     */
    public function testInstanceList($activityId)
    {
      $r = self::$f1->get("/events/v2/activities/{$activityId}/instances?pagesize=5");
      
      $instanceId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($instanceId, "No instance id returned");
      return $instanceId; 
    }

    /**
     * @group Instances
     * @depends testActivityList
     * @depends testInstanceList
     */
    public function testInstanceShow($activityId, $instanceId)
    {
     $r = self::$f1->get("/events/v2/activities/{$activityId}/instances/{$instanceId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }



    // ATTENDANCE START



}
?>