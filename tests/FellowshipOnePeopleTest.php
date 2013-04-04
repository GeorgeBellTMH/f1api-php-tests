<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Giving Realm.
  * @class FellowshipOnePeopleTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
  * @copyright 2013 Tracy Mazelin
  * @author Tracy Mazelin tracy.mazelin@activenetwork.com
  * @requires PHP 5.4, PECL OAuth, PHPUnit, XDebug (for logs)
  *
  */

require_once('../lib/FellowshipOne.php');
require_once('../lib/settings.php');

class FellowshipOnePeopleTest extends PHPUnit_Framework_TestCase
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

    // HOUSEHOLDS START
        
    /**
     * @group Households
     */
    public function testHouseholdSearch()
    {
      $r = self::$f1->get('/v1/households/search.json?createdDate=2009-01-01');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $householdId = $r['body']['results']['household'][0]['@id'];
    }


    /**
     * @group Households
     * @depends testHouseholdSearch
     */
    public function testHouseholdShow($householdId)
    {
      $r = self::$f1->get('/v1/households/'.$householdId.'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group Households
     * @depends testHouseholdSearch
     */
    public function testHouseholdEdit($householdId)
    {
      $model = self::$f1->get('/v1/households/'.$householdId.'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    /**
     * @group Households
     */
    public function testHouseholdNew()
    {
      $model = self::$f1->get('/v1/households/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

     /**
     * @group Households
     * @depends testHouseholdNew
     */
    public function testHouseholdCreate($model)
    {
      $model['household']['householdName'] = "API Create Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->post($model, '/v1/households.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['household']['@id'];
    }

    /**
     * @group Households
     * @depends testHouseholdEdit
     */
    public function testHouseholdUpdate($model)
    {
      $model['household']['householdName'] = "API Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->put($model, '/v1/households/'.$model['household']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }
       
  
    // Household Member Types
    
     /**
     * @group HouseholdMemberTypes
     */
    public function testHouseholdMemberTypesList()
    {
      $r = self::$f1->get('/v1/people/householdmembertypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $householdMemberTypeId = $r['body']['householdMemberTypes']['householdMemberType'][0]['@id'];
    }

    /**
     * @group HouseholdMemberTypes
     * @depends testHouseholdMemberTypesList
     */
    public function testHouseholdMemberTypesShow($householdMemberTypeId)
    {
      $r = self::$f1->get('/v1/people/householdmembertypes/'.$householdMemberTypeId.'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    // People
    
     /**
     * @group People
     */
    public function testPeopleSearch()
    {
      $r = self::$f1->get('/v1/people/search.json?createdDate=2009-01-01');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $personId = $r['body']['results']['person'][0]['@id'];
    }


    /**
     * @group People
     * @depends testPeopleSearch
     */
    public function testPeopleShow($personId)
    {
      $r = self::$f1->get('/v1/people/'.$personId.'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group People
     * @depends testPeopleSearch
     */
    public function testPeopleEdit($personId)
    {
      $model = self::$f1->get('/v1/people/'.$personId.'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    /**
     * @group People
     */
    public function testPeopleNew()
    {
      $model = self::$f1->get('/v1/people/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

     /**
     * @group People
     * @depends testPeopleNew
     * @depends testHouseholdCreate
     */
    public function testPeopleCreate($model, $householdId)
    {
      $model['person']['@householdID'] = $householdId;
      $model['person']['firstName'] = "API Unit";
      $model['person']['lastName'] = "Test";
      $model['person']['householdMemberType']['@id'] = "1"; 
      $model['person']['status']['@id'] = "1";
      $r = self::$f1->post($model, '/v1/people.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $model = $r['body'];
    }

    /**
     * @group People
     * @depends testPeopleCreate
     */
    public function testPeopleUpdate($model)
    {
      $model['person']['lastName'] = "Test Update";
      $r = self::$f1->put($model, '/v1/people/'.$model['person']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    
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