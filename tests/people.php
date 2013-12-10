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
        $env = 'uat';
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
      $r = self::$f1->get('/v1/people/search.json?createdDate=2001-01-01');
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

    
    // START PEOPLE ATTRIBUTES
    // Used New and Create first to make it easier to use random people accross different environments: 
   
    /**
     * @group PeopleAttributes
     */
    public function testPeopleAttributesNew()
    {
      $randomId = self::$f1->randomId('person');
      $model = self::$f1->get('/v1/people/'.$randomId['person'].'/attributes/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }


    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesNew
     */
    public function testPeopleAttributesCreate($model)
    {      
      $attributeGroup = self::$f1->get('/v1/people/attributeGroups.json');
      $model['attribute']['attributeGroup']['attribute']['@id'] = $attributeGroup['body']['attributeGroups']['attributeGroup'][0]['attribute'][0]['@id'];
      $model['attribute']['comment'] = "API Create Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->post($model, '/v1/people/'.$model['attribute']['person']['@id'].'/attributes.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $attribute = $r['body']['attribute'];
    }


    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesCreate
     */
    public function testPeopleAttributesList($attribute)
    {
      $r = self::$f1->get('/v1/people/'.$attribute['person']['@id'].'/attributes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesCreate
     */
    public function testPeopleAttributesShow($attribute)
    {
      $r = self::$f1->get('/v1/people/'.$attribute['person']['@id'].'/attributes/'.$attribute['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesCreate
     */
    public function testPeopleAttributesEdit($attribute)
    {
      $model = self::$f1->get('/v1/people/'.$attribute['person']['@id'].'/attributes/'.$attribute['@id'].'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesEdit
     */
    public function testPeopleAttributesUpdate($model)
    {
      $model['attribute']['comment'] = "API Update Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->put($model, '/v1/people/'.$model['attribute']['person']['@id'].'/attributes/'.$model['attribute']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group PeopleAttributes
     * @depends testPeopleAttributesCreate
     */
    public function testPeopleAttributesDelete($attribute)
    {
      $r = self::$f1->delete('/v1/people/'.$attribute['person']['@id'].'/attributes/'.$attribute['@id'].'.json');
      $this->assertEquals('204', $r['http_code'] );
    }


    // Start People Images
    // Used Create first to make it easier to use random people accross different environments: 
    
    /**
     * @group PeopleImages
     */
    public function testPeopleImagesCreate()
    {      
      $randomId = self::$f1->randomId('person');
      $checkNoImage = self::$f1->get('/v1/people/'.$randomId['person'].'.json');
      if($checkNoImage['body']['person']['@imageURI'] != null){
        $this->testPeopleImagesCreate();
        } else {
            $img = file_get_contents('../tests/img/smilley.jpg');
            $r = self::$f1->post_img($img, '/v1/people/'.$randomId['person'].'/images');    
            $this->assertEquals('201', $r['http_code']);
            return $personId = $randomId['person'];
          }
    }


    /**
     * @group PeopleImages
     * @depends testPeopleImagesCreate
     */
    public function testPeopleImagesShow($personId)
    {
      $r = self::$f1->get_img('/v1/people/'.$personId.'/images?size=S');
      $this->assertEquals('200', $r['http_code'] );
    }


    /**
     * @group PeopleImages
     * @depends testPeopleImagesCreate
     */
    public function testPeopleImagesUpdate($personId)
    {
      $r = self::$f1->get('/v1/people/'.$personId.'.json');
      $imageURI = $r['body']['person']['@imageURI'];
      $img = file_get_contents('img/smilley_update.jpg');
      $r = self::$f1->put_img($img, $imageURI);    
      $this->assertEquals('200', $r['http_code']);
    }
   
    // People Addresses Start
    
    /**
     * @group Addresses
     */
    public function testAddressesNew()
    {
      $randomId = self::$f1->randomId('person');
      $model = self::$f1->get('/v1/people/'.$randomId['person'].'/addresses/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }


    /**
     * @group Addresses
     * @depends testAddressesNew
     */
   public function testAddressesCreate($model)
    {      
      $model['address']['addressType']['@id'] = "2";
      $model['address']['address1'] = "API Create Address Unit Test";
      $r = self::$f1->post($model, '/v1/people/'.$model['address']['person']['@id'].'/addresses.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $address = $r['body']['address'];
    }

    /**
     * @group Addresses
     * @depends testAddressesNew
     */
    public function testAddressesList($model)
    {
      $r = self::$f1->get('/v1/people/'.$model['address']['person']['@id'].'/addresses.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    /**
     * @group Addresses
     * @depends testAddressesCreate
     */
    public function testAddressesShow($address)
    {
      $r = self::$f1->get('/v1/people/'.$address['person']['@id'].'/addresses/'.$address['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group Addresses
     * @depends testAddressesCreate
     */
    public function testAddressesEdit($address)
    {
      $model = self::$f1->get('/v1/people/'.$address['person']['@id'].'/addresses/'.$address['@id'].'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $address = $model['body'];
    }

    /**
     * @group Addresses
     * @depends testAddressesEdit
     */
    public function testAddressesUpdate($address)
    {
      $address['address']['address1'] = "API Update Address Unit Test";
      $r = self::$f1->put($address, '/v1/addresses/'.$address['address']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

     /**
     * @group Addresses
     * @depends testAddressesCreate
     */
    public function testAddressesDelete($address)
    {
      $r = self::$f1->delete('/v1/addresses/'.$address['@id'].'.json');
      $this->assertEquals('204', $r['http_code'] );
    }

    // Start Address Types
    
    /**
     * @group AddressTypes
     */
    public function testAddressTypesList()
    {
      $r = self::$f1->get('/v1/addresses/addresstypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['addressTypes'];
    }

    /**
     * @group AddressTypes
     * @depends testAddressTypesList
     */
    public function testAddressTypesShow($addressTypes)
    {
      $r = self::$f1->get('/v1/addresses/addresstypes/'.$addressTypes['addressType'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    
    // Start Attribute Groups
    
    /**
     * @group AttributeGroups
     */
    public function testAttributeGroupsList()
    {
      $r = self::$f1->get('/v1/people/attributegroups.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['attributeGroups'];
    }

    /**
     * @group AttributeGroups
     * @depends testAttributeGroupsList
     */
    public function testAttributeGroupsShow($attributeGroups)
    {
      $r = self::$f1->get('/v1/people/attributegroups/'.$attributeGroups['attributeGroup'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    // Start Attributes
    
    /**
     * @group Attributes
     * @depends testAttributeGroupsList
     */
    public function testAttributeList($attributeGroups)
    {
      $r = self::$f1->get('/v1/people/attributegroups/'.$attributeGroups['attributeGroup'][0]['@id'].'/attributes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['attributes'];
    }

    /**
     * @group Attributes
     * @depends testAttributeGroupsList
     * @depends testAttributeList
     */
    public function testAttributeShow($attributeGroup, $attribute)
    {
      // $r = self::$f1->get('/v1/people/attributegroups/'.$attributeGroup['attributeGroup'][0]['@id'].'/attributes/'.$attribute['attribute'][0]['@id'].'.json');
      // $this->assertEquals('200', $r['http_code'] );
      // $this->assertNotEmpty($r['body'], "No Response Body");
      // returning 500 - error in api?
      // $uri = "https://tmazelin.staging.fellowshiponeapi.com/v1/People/AttributeGroups/35240/Attributes/468099"
    }
   
  
    // Communications Start
          
    /**
     * @group Communications
     */
    public function testCommunicationsNew()
    {
      $randomId = self::$f1->randomId('person');
      $model = self::$f1->get('/v1/people/'.$randomId['person'].'/communications/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }


    /**
     * @group Communications
     * @depends testCommunicationsNew
     */
    public function testCommunicationsCreate($model)
    {      
      $model['communication']['communicationType']['@id'] = "2";
      $model['communication']['communicationValue'] = "333-333-3333";
      $r = self::$f1->post($model, '/v1/people/'.$model['communication']['person']['@id'].'/communications.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $communication = $r['body']['communication'];
    } 

    /**
     * @group Communications
     * @depends testCommunicationsNew
     */
    public function testCommunicationsList($model)
    {
      $r = self::$f1->get('/v1/people/'.$model['communication']['person']['@id'].'/communications.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    /**
     * @group Communications
     * @depends testCommunicationsCreate
     */
    public function testCommunicationsShow($communication)
    {
      $r = self::$f1->get('/v1/people/'.$communication['person']['@id'].'/communications/'.$communication['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group Communications
     * @depends testCommunicationsCreate
     */
    public function testCommunicationsEdit($communication)
    {
      $model = self::$f1->get('/v1/people/'.$communication['person']['@id'].'/communications/'.$communication['@id'].'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $communication = $model['body'];
    }

    /**
     * @group Communications
     * @depends testCommunicationsEdit
     */
    public function testCommunicationsUpdate($communication)
    {
      $communication['communication']['communicationValue'] = "222-222-2222";
      $r = self::$f1->put($communication, '/v1/communications/'.$communication['communication']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group Communications
     * @depends testCommunicationsCreate
     */
    public function testCommunicationsDelete($communication)
    {
      $r = self::$f1->delete('/v1/communications/'.$communication['@id'].'.json');
      $this->assertEquals('204', $r['http_code'] );
    }

    // Communication Types Start
        
    /**
     * @group CommunicationTypes
     */
    public function testCommunicationTypeList()
    {
      $r = self::$f1->get('/v1/communications/communicationtypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['communicationTypes'];
    }

    /**
     * @group CommunicationTypes
     * @depends testCommunicationTypeList
     */
    public function testCommunicationTypeShow($communicationType)
    {
      $r = self::$f1->get('/v1/communications/communicationtypes/'.$communicationType['communicationType'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  
     
    
    // Denominations Start

    /**
     * @group Denominations
     */
    public function testDenominationsList()
    {
      $r = self::$f1->get('/v1/people/denominations.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['denominations'];
    }

    /**
     * @group Denominations
     * @depends testDenominationsList
     */
    public function testDenominationsShow($denominations)
    {
      $r = self::$f1->get('/v1/people/denominations/'.$denominations['denomination'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  


    // Occupations Start

    /**
     * @group Occupations
     */
    public function testOccupationsList()
    {
      $r = self::$f1->get('/v1/people/occupations.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['occupations'];
    }

    /**
     * @group Occupations
     * @depends testOccupationsList
     */
    public function testOccupationsShow($occupations)
    {
      $r = self::$f1->get('/v1/people/occupations/'.$occupations['occupation'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  


    // Schools Start

    /**
     * @group Schools
     */
    public function testSchoolsList()
    {
      $r = self::$f1->get('/v1/people/schools.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['schools'];
    }

    /**
     * @group Schools
     * @depends testSchoolsList
     */
    public function testSchoolsShow($schools)
    {
      $r = self::$f1->get('/v1/people/schools/'.$schools['school'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  


    // Statuses Start

    /**
     * @group Statuses
     */
    public function testStatusesList()
    {
      $r = self::$f1->get('/v1/people/statuses.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['statuses'];
    }

    /**
     * @group Statuses
     * @depends testStatusesList
     */
    public function testStatusesShow($statuses)
    {
      $r = self::$f1->get('/v1/people/statuses/'.$statuses['status'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  

    // Sub Statuses Start

    /**
     * @group SubStatuses
     */
    public function testSubStatusesList()
    {
      $r = self::$f1->get('/v1/people/statuses/1/substatuses.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['subStatuses'];
    }

    /**
     * @group SubStatuses
     * @depends testSubStatusesList
     */
    public function testSubStatusesShow($subStatuses)
    {
      $r = self::$f1->get('/v1/people/statuses/1/substatuses/'.$subStatuses['subStatus'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  
    
    // People Lists Start
    // Lists: List (3rd) | Show (3rd)
    // Members: List (3rd) | Show (3rd)
    // Users: List (3rd) | Show (3rd)

    /**
     * @group PeopleLists
     */
    public function testPeopleListsList()
    {
      $r = self::$f1->get('/v1/people/lists.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['peopleLists'];
    }

    /**
     * @group PeopleLists
     * @depends testPeopleListsList
     */
    public function testPeopleListsShow($lists)
    {
      $r = self::$f1->get('/v1/people/lists/'.$lists['peopleList'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group PeopleListsMembers
     * @depends testPeopleListsShow
     */
    public function testPeopleListMembersList($list)
    {
      $r = self::$f1->get('/v1/people/lists/'.$lists['peopleList'][0]['@id'].'/members.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['peopleList']['members'];
    }

    /**
     * @group PeopleListsMembers
     * @depends testPeopleListsShow
     * @depends testPeopleListMembersList
     */
    public function testPeopleListMembersShow($list, $members)
    {
      $r = self::$f1->get('/v1/people/lists/'.$lists['peopleList'][0]['@id'].'/members/'.$members['members'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group PeopleListsUsers
     * @depends testPeopleListsShow
     */
    public function testPeopleListUsersList($list)
    {
      $r = self::$f1->get('/v1/people/lists/'.$lists['peopleList'][0]['@id'].'/users.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['peopleList']['users'];
    }

    /**
     * @group PeopleListsUsers
     * @depends testPeopleListsShow
     * @depends testPeopleListUsersList
     */
    public function testPeopleListUsersShow($list, $users)
    {
      $r = self::$f1->get('/v1/people/lists/'.$lists['peopleList'][0]['@id'].'/users/'.$users['users'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    // Requirements Start
    // Requirements: List (3rd) | Show (3rd) | Edit (3rd) | New (3rd) | Create (3rd) | Update (3rd)
    
    /**
     * @group Requirements
     */
    public function testRequirementsNew()
    {
      $model = self::$f1->get('/v1/requirements/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }


    /**
     * @group Requirements
     * @depends testRequirementsNew
     */
   public function testRequirementsCreate($model)
    {      
      $model['requirement']['name'] = "API Create Requirement Test - ".self::$today->format("Y-m-d H:i:s");
      $model['requirement']['quantity'] = "1"; //new returns quantity of null.  Required.
      $r = self::$f1->post($model, '/v1/requirements.json');
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $requirement = $r['body']['requirement'];
    }

     /**
     * @group Requirements
     * @depends testRequirementsNew
     */
    public function testRequirementsList($model)
    {
      $r = self::$f1->get('/v1/requirements.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    /**
     * @group Requirements
     * @depends testRequirementsCreate
     */
    public function testRequirementsShow($requirement)
    {
      $r = self::$f1->get('/v1/requirements/'.$requirement['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group Requirements
     * @depends testRequirementsCreate
     */
    public function testRequirementsEdit($requirement)
    {
      $model = self::$f1->get('/v1/requirements/'.$requirement['@id'].'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $requirement = $model['body'];
    }

    /**
     * @group Requirements
     * @depends testRequirementsEdit
     */
    public function testRequirementsUpdate($requirement)
    {
      $requirement['requirement']['name'] = "API Requirement Test - ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->put($requirement, '/v1/requirements/'.$requirement['requirement']['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }


    // Requirement Statuses Start

    /**
     * @group RequirementStatuses
     */
    public function testRequirementStatusesList()
    {
      $r = self::$f1->get('/v1/requirements/requirementstatuses.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['requirementStatuses'];
    }

    /**
     * @group RequirementStatuses
     * @depends testRequirementStatusesList
     */
    public function testRequirementStatusesShow($requirementStatuses)
    {
      $r = self::$f1->get('/v1/requirements/requirementstatuses/'.$requirementStatuses['requirementStatus'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  


    // Background Check Statuses Start

    /**
     * @group BackgroundCheckStatuses
     */
    public function testBackgroundCheckStatusesList()
    {
      $r = self::$f1->get('/v1/requirements/backgroundcheckstatuses.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
      return $r['body']['backgroundCheckStatuses'];
    }

    /**
     * @group BackgroundCheckStatuses
     * @depends testBackgroundCheckStatusesList
     */
    public function testBackgroundCheckStatusesShow($backgroundCheckStatuses)
    {
      $r = self::$f1->get('/v1/requirements/backgroundcheckstatuses/'.$backgroundCheckStatuses['backgroundCheckStatus'][0]['@id'].'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response Body");
    }  


    // People Requirements Start

    /**
     * @group PeopleRequirements
     */
    public function testPeopleRequirementsNew()
    {
      $model = self::$f1->get('/v1/people/requirements/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    //Documentation is missing a bunch of required fields for PeopleRequirementsCreate
    //Background Check Module must also be enabled to pass in a peopleRequirement otherwise 500 thrown.  Skipping the other peopleRequirement tests.
    
    /**
     * @group PeopleRequirements
     * @depends testPeopleRequirementsNew
     */
    public function testPeopleRequirementsCreate($model)
    {            
      // $randomId = self::$f1->randomId('person');
      // $model['peopleRequirement']['person']['@id'] = $randomId['person'];
      // $model['peopleRequirement']['requirement']['@id'] = "";//$requirement['@id'];
      // $model['peopleRequirement']['requirementStatus']['@id'] = "1";
      // $model['peopleRequirement']['requirementDate'] = "";
      // $model['peopleRequirement']['staffPerson']['@id'] = "";//"40919398";
      // $model['peopleRequirement']['backgroundCheck']['backgroundCheckStatus']['@id'] = "1";
      // $r = self::$f1->post($model, '/v1/people/'.$randomId['person'].'/requirements.json');
      // $this->assertEquals('201', $r['http_code']);
      // $this->assertNotEmpty($r['body'], "No Response Body");
      // return $peopleRequirement = $r['body']['requirement'];
    }       
}