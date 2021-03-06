<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Events Realm.
  * @class FellowshipOneEventsTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
  * @author Tracy Mazelin tracy.mazelin@activenetwork.com
  * @requires PHP 5.4, PECL OAuth, PHPUnit, XDebug (for logs)
  *
  */

require_once('../lib/FellowshipOne.php');
require_once('../lib/settings.php');

class FellowshipOneEventsTest extends PHPUnit_Framework_TestCase
{
    protected static $f1;
    protected static $today;
    
    public static function setupBeforeClass()
    {
        global $settings,$env;
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']);        
    }


    /**
     * @group Events
     */
    public function testEventList()
    {
       $r = self::$f1->get('/events/v1/events.json');   
       $eventId = $r['body']['events']['event'][0]['@id'];
       $this->assertEquals('200', $r['http_code']);
       $this->assertNotEmpty($eventId, 'No Event ID');
       return $eventId;
    }

    /**
     * @group Events
     * @depends testEventList
     */
    public function testEventShow($eventId)
    {
       $r = self::$f1->get('/events/v1/events/'.$eventId.'.json');
       $this->assertEquals('200', $r['http_code']);    
       $this->assertNotEmpty($r['body'], 'Could not Show Event');
       
    }

    /**
     * @group Events
     * @depends testEventList
     */
     public function testEventEdit($eventId)
    {
      $model = self::$f1->get('/events/v1/events/'.$eventId.'/edit.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Model Returned");
      return $model['body'];
    }

    /**
     * @group Events
     * @depends testEventList
     * @depends testEventEdit
     */    
    public function testEventUpdate($eventId, $model)
    {
      $model['event']['name'] = "API Update Unit Test.".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->put($model, '/events/v1/events/'.$eventId.'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    /**
     * @group Schedules
     * @depends testEventList
     */
    //public function testScheduleList($eventId)
    //{
      //$r = self::$f1->get('/events/v1/events/'.$eventId.'/schedules.json');
      //$scheduleId = $r['body']['schedules']['schedule'][0]['@id'];
      //$this->assertEquals('200', $r['http_code']);
      //$this->assertNotEmpty($scheduleId, "No Schedule ID");
      //return $scheduleId;
    //}
	
	//Overwite by grace zhang testcase testScheduleList:find a event which has a valid schedule
	public function testScheduleList()
    {
	  $r1 = self::$f1->get('/events/v1/events.json');   
      $eventId = $r1['body']['events']['event'][0]['@id'];
      $this->assertEquals('200', $r1['http_code']);     
      $eventIdCount = count($r1['body']['events']['event']);	  
	  print_r("eventIdCount =");	
	  print_r($eventIdCount); 
		
	  for ($i = 0; $i < $eventIdCount; $i++) 
	  {
		$eventId = $r1['body']['events']['event'][$i]['@id'];
	    $r = self::$f1->get('/events/v1/events/'.$eventId.'/schedules.json');
        $scheduleId = $r['body']['schedules']['schedule'][0]['@id'];
        if($scheduleId != null)
		{
			 $this->assertEquals('200', $r['http_code']);
             $this->assertNotEmpty($scheduleId, "No Schedule ID");
             return $scheduleId;
			break;
		}
	  }			
      
    }

    /**
     * @group Schedules
     * @depends testEventList
     * @depends testScheduleList
     */
    public function testScheduleShow($eventId, $scheduleId)
    {
      $r = self::$f1->get('/events/v1/events/'.$eventId.'/schedules/'.$scheduleId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    } 

    /**
     * @group Schedules
     * @depends testEventList
     * @depends testScheduleList
     */
    public function testScheduleEdit($eventId, $scheduleId)
    {
       $model = self::$f1->get('/events/v1/events/'.$eventId.'/schedules/'.$scheduleId.'/edit.json');
       $this->assertEquals('200', $model['http_code']);
       $this->assertNotEmpty($model['body'], "No Response Body");
       return $model['body'];
    }

   /**
     * @group Schedules
     * @depends testEventList
     */
    public function testScheduleNew($eventId)
    {
      $model = self::$f1->get('/events/v1/events/'.$eventId.'/schedules/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }

    /**
     * @group Schedules
     * @depends testEventList
     * @depends testScheduleNew
     */
    public function testScheduleCreate($eventId, $model)
    {
      $model['schedule']['name'] = "API Create Unit Test.".self::$today->format("Y-m-d H:i:s");
      $model['schedule']['startTime'] = self::$today->format(DATE_ATOM);
      $model['schedule']['startDate'] = self::$today->format(DATE_ATOM);
      $model['schedule']['recurrenceType']['@id'] = "2";
      $model['schedule']['recurrences']['recurrence']['recurrenceWeekly']['recurrenceFrequency'] = "5";
      $model['schedule']['recurrences']['recurrence']['recurrenceWeekly']['occurOnSunday'] = "true";
      $r = self::$f1->post($model, '/events/v1/events/'.$eventId.'/schedules.json');
      $scheduleId = $r['body']['schedule']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($scheduleId, "No Response Body");
      return $scheduleId;

    }

    /**
     * @group Schedules
     * @depends testEventList
     * @depends testScheduleCreate
     * @depends testScheduleNew
     */
    public function testScheduleUpdate($eventId, $scheduleId, $model)
    {
      $model['schedule']['name'] = "API Update Unit Test.".self::$today->format("Y-m-d H:i:s");
      $model['schedule']['startTime'] = self::$today->format(DATE_ATOM);
      $model['schedule']['startDate'] = self::$today->format(DATE_ATOM);
      $model['schedule']['recurrenceType']['@id'] = "2";
      $model['schedule']['recurrences']['recurrence']['recurrenceWeekly']['recurrenceFrequency'] = "10";
      $model['schedule']['recurrences']['recurrence']['recurrenceWeekly']['occurOnSunday'] = "true";
      $r = self::$f1->put($model, '/events/v1/events/'.$eventId.'/schedules/'.$scheduleId .'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

    /**
     * @group Schedules
     * @depends testEventList
     * @depends testScheduleCreate
     */
    public function testScheduleDelete($eventId, $scheduleId)
    {
      $r = self::$f1->delete('/events/v1/events/'.$eventId.'/schedules/'.$scheduleId.'.json'); 
      $this->assertEquals('204', $r['http_code']);   
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }



    /**
     * @group Locations
     * @depends testEventList
     */ 
    //public function testLocationList($eventId)
    //{
      //$r = self::$f1->get('/events/v1/events/'.$eventId.'/locations.json');
      //$locationId = $r['body']['locations']['location'][0]['@id'];
      //$this->assertEquals('200', $r['http_code']);
      //$this->assertNotNull($locationId, "No Response Body");
      //return $locationId;
    //}
    //Overwite by grace zhang testcase testLocationList:find a event which has a valid location
	public function testLocationList()
    {
      $r1 = self::$f1->get('/events/v1/events.json');   
      $eventId = $r1['body']['events']['event'][0]['@id'];
      $this->assertEquals('200', $r1['http_code']);     
      $eventIdCount = count($r1['body']['events']['event']);	  
	  print_r("eventIdCount =");	
	  print_r($eventIdCount); 
		
	  for ($i = 0; $i < $eventIdCount; $i++) 
	  {
		$eventId = $r1['body']['events']['event'][$i]['@id'];
	    $r = self::$f1->get('/events/v1/events/'.$eventId.'/locations.json');
        $locationId = $r['body']['locations']['location'][0]['@id'];
        if($locationId != null)
		{
			$this->assertEquals('200', $r['http_code']);
            $this->assertNotNull($locationId, "No Response Body");
            return $locationId;
			break;
		}
	  }			
     
    }
    /**
     * @group Locations
     * @depends testEventList
     * @depends testLocationList
     */
    public function testLocationShow($eventId, $locationId)
    {
      $r = self::$f1->get('/events/v1/events/'.$eventId.'/locations/'.$locationId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }

    /**
     * @group Locations
     * @depends testEventList
     * @depends testLocationList
     */
    public function testLocationEdit($eventId, $locationId)
    {
       $model = self::$f1->get('/events/v1/events/'.$eventId.'/locations/'.$locationId.'/edit.json');
       $this->assertEquals('200', $model['http_code']);
       $this->assertNotEmpty($model['body'], "No Response Body");
       return $model;
    }

    /**
     * @group Locations
     * @depends testEventList
     */ 
    public function testLocationNew($eventId)
    {
      $model = self::$f1->get('/events/v1/events/'.$eventId.'/locations/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }

   
    /**
     * @group Locations
     * @depends testEventList
     * @depends testLocationNew
     */
    public function testLocationCreate($eventId, $model)
    {
      $model['location']['name'] = "API Create Unit Test.".self::$today->format("Y-m-d H:i:s");
      $model['location']['isOnline'] = "true";
      $r = self::$f1->post($model, '/events/v1/events/'.$eventId.'/locations.json');
      $this->assertNotEmpty($r['body'], "No Response Body"); 
      $locationId = $r['body']['location']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($locationId, "No Response Body");
      return $locationId;
    }

    /**
     * @group Locations
     * @depends testEventList
     * @depends testLocationCreate
     * @depends testLocationNew
     */
    public function testLocationUpdate($eventId, $locationId, $model)
    {
      $model['location']['name'] = "API Update Unit Test.".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->post($model, '/events/v1/events/'.$eventId.'/locations/'.$locationId.'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

    /**
     * @group Locations
     * @depends testEventList
     * @depends testLocationCreate
     */
    public function testLocationDelete($eventId, $locationId)
    {
      $r = self::$f1->delete('/events/v1/events/'.$eventId.'/locations/'.$locationId.'.json');
      $this->assertEquals('204', $r['http_code']);    
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }

    /**
     * @group RecurrenceTypes
     */
    public function testRecurrenceTypeList()
    {
      $r = self::$f1->get('/events/v1/recurrencetypes.json');
     
      $recurrenceTypeId = $r['body']['recurrenceTypes']['recurrenceType'][1]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($recurrenceTypeId, "No recurrence id returned");
      return $recurrenceTypeId; 
    }

    /**
     * @group RecurrenceTypes
     * @depends testRecurrenceTypeList
     */
    //public function testRecurrenceTypeShow($recurrenceTypeId)
    //{
     //$r = self::$f1->get('/events/v1/recurrencetypes/'.$recurrenceTypeId .'.json');
     //$this->assertEquals('200', $r['http_code']);
     //$this->assertNotEmpty($r['body'], "No Response Body");   
    //}
	
	//Overwite by grace zhang testcase testRecurrenceTypeShow:find a valid recurrenceType
	public function testRecurrenceTypeShow()
    {
	  $r1 = self::$f1->get('/events/v1/recurrencetypes.json');          
      $this->assertEquals('200', $r1['http_code']);      
	  $recurrenceTypeIdCount = count($r1['body']['recurrenceTypes']['recurrenceType']);	  
	  print_r("recurrenceTypeIdCount =");	
	  print_r($recurrenceTypeIdCount); 
      for ($i = 0; $i < $recurrenceTypeIdCount; $i++) 
	  {
		$recurrenceTypeId = $r1['body']['recurrenceTypes']['recurrenceType'][$i]['@id'];
	    $r = self::$f1->get('/events/v1/recurrencetypes/'.$recurrenceTypeId .'.json');
       
        if( $r['http_code'] == '200')
		{
			 $this->assertEquals('200', $r['http_code']);
             $this->assertNotEmpty($r['body'], "No Response Body");   
			break;
		}
	  }			
     
    }

    /**
     * @group AttendanceSummaries
     * 
     */
    public function testAttendanceSummmariesSearch()
    {
     $r = self::$f1->get('/events/v1/attendanceSummaries/Search?attendanceContextTypeID=1');
     $attendanceSummaryId = $r['body']['results']['attendanceSummary'][0]['@id'];
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($attendanceSummaryId, "No attendance summary id returned");
     return $attendanceSummaryId;   
    }

    /**
     * @group AttendanceSummaries
     * @depends testAttendanceSummmariesSearch
     * 
     */
    public function testAttendanceSummmariesShow($attendanceSummaryId)
    {
     $r = self::$f1->get('/events/v1/attendanceSummaries/'.$attendanceSummaryId);
     $this->assertEquals('200', $r['http_code']);
    }
}
?>