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

class FellowshipOneActivitesTest extends PHPUnit_Framework_TestCase
{
    protected static $f1;
    protected static $today;
    protected static $personId;
    
    public static function setupBeforeClass()
    {
        global $settings;
        $env = 'qa';
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']); 
        $person = self::$f1->get("/v1/people/search?searchfor=Paul");
        self::$personId = $person['body']['results']['person'][0]['@id'];   
   
    }
   
    // MINISTRIES START
    
     /**
     * @group Ministries
     */
    public function testMinistryList()
    {
      $r = self::$f1->get('/activities/v1/ministries?pagesize=5');
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
     $r = self::$f1->get("/activities/v1/ministries/{$ministryId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }
    
    // ACTIVITIES START
    
    /**
     * @group Activities
     */
    public function testActivityList()
    {
      $r = self::$f1->get('/activities/v1/activities?pagesize=5');
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
     $r = self::$f1->get("/activities/v1/activities/{$activityId}");
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
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/assignments");
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
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/assignments/{$assignmentId}");
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    } 
   
    /**
     * @group Assignments
     */
    public function testAssignmentNew()
    {
      $model = self::$f1->get("/activities/v1/assignments/new");
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
      $model['type']['id'] = 1;
      $model['person']['id'] = self::$personId;
      $model['activity']['id'] = $activityId;
      $r = self::$f1->post($model, "/activities/v1/activities/{$activityId}/assignments");
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
      //$assignment['person']['id'] = self::$personId;
      $r = self::$f1->put($assignment, "/activities/v1/activities/{$activityId}/assignments/{$assignmentId}");
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
      $r = self::$f1->delete("/activities/v1/activities/{$activityId}/assignments/{$assignmentId}"); 
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
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/schedules?pagesize=5");
      
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
     $r = self::$f1->get("/activities/v1/activities/{$activityId}/schedules/{$scheduleId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // ROSTERS START

     /**
     * @group Rosters
     * @depends testActivityList
     */
    public function testRosterList($activityId)
    {
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/rosters?pagesize=5");
      
      $rosterId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($rosterId, "No roster id returned");
      return $rosterId; 
    }

    /**
     * @group Rosters
     * @depends testActivityList
     * @depends testRosterList
     */
    public function testRosterShow($activityId, $rosterId)
    {
     $r = self::$f1->get("/activities/v1/activities/{$activityId}/rosters/{$rosterId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // ROSTERFOLDERS START

     /**
     * @group RosterFolders
     * @depends testActivityList
     */
    public function testRosterFolderList($activityId)
    {
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/rosterfolders?pagesize=5");
      
      $rosterFolderId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($rosterFolderId, "No roster id returned");
      return $rosterFolderId; 
    }

    /**
     * @group RosterFolders
     * @depends testActivityList
     * @depends testRosterFolderList
     */
    public function testRosterFolderShow($activityId, $rosterFolderId)
    {
     $r = self::$f1->get("/activities/v1/activities/{$activityId}/rosterfolders/{$rosterFolderId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // INSTANCES START

    /**
     * @group Instances
     * @depends testScheduleList
     */
    public function testInstanceList($scheduleId)
    {
      $r = self::$f1->get("/activities/v1/schedules/{$scheduleId}/instances?pagesize=5");
      
      $instanceId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($instanceId, "No instance id returned");
      return $instanceId; 
    }

    /**
     * @group Instances
     * @depends testScheduleList
     * @depends testInstanceList
     */
    public function testInstanceShow($scheduleId, $instanceId)
    {
     $r = self::$f1->get("/activities/v1/schedules/{$scheduleId}/instances/{$instanceId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // ATTENDANCE START
      
    /**
     * @group Attendances
     */
    public function testAttendanceNew()
    {
      $model = self::$f1->get("/activities/v1/attendances/new");
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }
 
    /**
     * @group Attendances
     * @depends testActivityList
     * @depends testInstanceList
     * @depends testRosterList
     * @depends testAttendanceNew
     */
    public function testAttendanceCreate($activityId, $instanceId, $rosterId, $model)
    {
      $model['person']['id'] = self::$personId;
      $model['activity']['id'] = $activityId;
      $model['instance']['id'] = $instanceId;
      $model['roster']['id'] = $rosterId;
      $model['type']['id'] = 1;
      $r = self::$f1->post($model, "/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances");
      $attendanceId = $r['body']['id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($attendanceId, "No Response Body");
      return $attendanceId;
    }
 
     /**
     * @group Attendances
     * @depends testActivityList
     * @depends testInstanceList
     */
    public function testAttendanceList($activityId, $instanceId)
    {
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances");
      $attendanceId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($attendanceId, "No Attendance ID");
      return $attendanceId;
    }
 
    /**
     * @group Attendances
     * @depends testActivityList
     * @depends testInstanceList
     * @depends testAttendanceCreate
     */
    public function testAttendanceShow($activityId, $instanceId, $attendanceId)
    {
      $r = self::$f1->get("/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances/{$attendanceId}");
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    } 
 
    /**
     * @group Attendances
     * @depends testActivityList
     * @depends testInstanceList
     * @depends testAttendanceCreate
     */
    public function testAttendanceUpdate($activityId, $instanceId, $attendanceId)
    {
      $attendance = self::$f1->get("/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances/{$attendanceId}");
      $r = self::$f1->put($attendance['body'], "/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances/{$attendanceId}");
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }
 
    /**
     * @group Attendances
     * @depends testActivityList
     * @depends testInstanceList
     * @depends testAttendanceCreate
     */
    public function testAttendanceDelete($activityId, $instanceId, $attendanceId)
    {
      
      $r = self::$f1->delete("/activities/v1/activities/{$activityId}/instances/{$instanceId}/attendances/{$attendanceId}"); 
      $this->assertEquals('204', $r['http_code']);   
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }

    // TYPES START
 
    /**
     * @group Types
     */
    public function testTypeList()
    {
      $r = self::$f1->get("/activities/v1/types?pagesize=5");
      $typeId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($typeId, "No type id returned");
      return $typeId; 
    }
 
    /**
     * @group Types
     * @depends testTypeList
     */
    public function testTypeShow($typeId)
    {
     $r = self::$f1->get("/activities/v1/types/{$typeId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

    // HEAD COUNTS START

    /**
     * @group HeadCounts
     */
    public function testHeadCountCreate()
    {
      $r = self::$f1->get("/activities/v1/headcounts/new");
      $model = $r['body'];
      $model['instance']['id'] = 210377;
      $model['roster']['id'] = 12867;
      $model['headCount'] = 120;
      $r = self::$f1->post($model, "/activities/v1/headcounts");
      $headCountId = $r['body']['id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($headCountId, "No Response Body");
      return $headCountId;
    }
 
     /**
     * @group HeadCounts
     */
    public function testHeadCountList()
    {
     
      $r = self::$f1->get("/activities/v1/instances/210377/headcounts");
      $headCountId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($headCountId, "No HeadCount ID");
      return $headCountId;
    }
 
    /**
     * @group HeadCounts
     * @depends testHeadCountList
     */
    public function testHeadCountShow($headCountId)
    {
      $r = self::$f1->get("/activities/v1/headcounts/{$headCountId}");
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    } 
 
    /**
     * @group HeadCounts
     * @depends testHeadCountCreate
     */
    public function testHeadCountUpdate($headCountId)
    {
      $headCount = self::$f1->get("/activities/v1/headcounts/{$headCountId}");
      $r = self::$f1->put($headCount['body'], "/activities/v1/headcounts/{$headCountId}");
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }
 
    /**
     * @group HeadCounts
     * @depends testHeadCountCreate
     */
    public function testHeadCountDelete($headCountId)
    {
      
      $r = self::$f1->delete("/activities/v1/headcounts/{$headCountId}"); 
      $this->assertEquals('204', $r['http_code']);   
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }

    // Rooms start
     /**
     * @group Rooms
     */
    public function testRoomList()
    {
      $r = self::$f1->get('/activities/v1/rooms?pagesize=5');
      $roomId = $r['body'][0]['id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($roomId, "No room id returned");
      return $roomId; 
    }
    
    /**
     * @group Rooms
     * @depends testRoomList
     */
    public function testRoomShow($roomId)
    {
     $r = self::$f1->get("/activities/v1/rooms/{$roomId}");
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");   
    }

}
?>