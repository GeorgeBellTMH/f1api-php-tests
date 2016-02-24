<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Groups Realm.
  * @class FellowshipOneGroupsTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
  * @author Tracy Mazelin tracy.mazelin@activenetwork.com
  * @requires PHP 5.4, PECL OAuth, PHPUnit, XDebug (for logs)
  *
  */

require_once('../lib/FellowshipOne.php');
require_once('../lib/settings.php');

class FellowshipOneGroupsTest extends PHPUnit_Framework_TestCase
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
    * @group GroupTypes
    */
    //public function testGroupTypeList()
    //{
      //$r = self::$f1->get('/groups/v1/grouptypes.json');
      //$groupTypeId = $r['body']['groupTypes']['groupType'][0]['@id'];
      //$this->assertEquals('200', $r['http_code']);
      //$this->assertNotEmpty($groupTypeId, "No group type id returned");
      //return $groupTypeId; 
    //}
	
	//Overwite by grace zhang testcase testGroupTypeList:find a real valid grouptype to used by other cases
    public function testGroupTypeList()
    {
      $r0 = self::$f1->get('/groups/v1/grouptypes.json'); 
      $this->assertEquals('200', $r0['http_code']);     
	  $groupTypeIdCount = count($r0['body']['groupTypes']['groupType']);
      print_r("groupTypeIdCount =");	
	  print_r($groupTypeIdCount);
	  for ($i = 0; $i < $groupTypeIdCount; $i++) 
	  {
	    $groupTypeId = $r0['body']['groupTypes']['groupType'][$i]['@id'];	
        print_r("groupTypeId =");	
	    print_r($groupTypeId);		
	    $r1 = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId.'/groups.json');
        $this->assertEquals('200', $r1['http_code']);  
	    $groupIdCount = count($r1['body']['groups']['group']);	  
	    print_r("groupIdCount =");	
	    print_r($groupIdCount);
	    
		$index =0;
	    for ($j = 0; $j < $groupIdCount; $j++) 
	    {
		  $groupId = $r1['body']['groups']['group'][$j]['@id'];
	      $r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members.json');
          $memberId = $r['body']['members']['member'][0]['@id'];
          if($memberId != null)
		  {
			  $model = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'/edit.json');
              $this->assertEquals('200', $model['http_code']);
              $this->assertNotEmpty($model['body'], "No Response Body");            
			  print_r("testMemberEdit - model=");
		      print_r($model['body']);
			  break;
		  }
		  $index++;
	    }
		print_r("index =");	
	    print_r($index);
		if($index==$groupIdCount)
		{
			continue;
		}
		else
		{
			 return $groupTypeId; 
		}
	  }		
      
    }

    
    /**
     * @group GroupTypes
     * @depends testGroupTypeList
     */
    public function testGroupTypeShow($groupTypeId)
    {
	 print_r("groupTypeId in testGroupTypeShow =");	
	 print_r($groupTypeId);		
     $r = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId .'.json');
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");
    }

    /**
     * @group GroupTypes
     */
    public function testGroupTypeSearch()
    {
      $r = self::$f1->get('/groups/v1/grouptypes/search.json?issearchable=true');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response");
    }

    /**
     * @group Groups
     * @depends testGroupTypeList
     */
    public function testGroupSearch()
    {
      $r = self::$f1->get('/groups/v1/groups/search.json?issearchable=true');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response");
    }

    /**
     * @group Groups
     * @depends testGroupTypeList
     */
    public function testGroupList($groupTypeId)
    {
      $r = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId.'/groups.json');
      $groupId = $r['body']['groups']['group'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($groupId, "No Group ID");
      return $groupId;
    }

    /**
     * @group Groups
     * @depends testGroupList
     */
    public function testGroupShow($groupId)
    {
      $r = self::$f1->get('/groups/v1/groups/'.$groupId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }

    /**
     * @group Groups
     * @depends testGroupList
     */
    public function testGroupEdit($groupId)
    {
       $model = self::$f1->get('/groups/v1/groups/'.$groupId.'/edit.json');
       $this->assertEquals('200', $model['http_code']);
       $this->assertNotEmpty($model['body'], "No Response Body");
       return $model['body'];
    }

   /**
     * @group Groups
     */
    public function testGroupNew()
    {
      $model = self::$f1->get('/groups/v1/groups/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    /**
     * @group Groups
     * @depends testGroupNew
     * @depends testGroupTypeList
     */
    public function testGroupCreate($model, $groupTypeId)
    {
      $model['group']['name'] = "API Create Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $model['group']['startDate'] = self::$today->format(DATE_ATOM);
      $model['group']['isOpen'] = "true";
      $model['group']['isPublic'] = "true";
      $model['group']['hasChildcare'] = "true";
      $model['group']['isSearchable'] = "true";
      $model['group']['groupType']['@id'] = $groupTypeId;
      $model['group']['timeZone']['@id'] = "115";
      $r = self::$f1->post($model, '/groups/v1/groups.json');
      $groupId = $r['body']['group']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($groupId, "No Response Body");
      return $groupId;
    }

    /**
     * @group Groups
     * @depends testGroupCreate
     * @depends testGroupTypeList
     * @depends testGroupEdit
     */
    public function testGroupUpdate($groupId, $groupTypeId, $model)
    {
      $model['group']['name'] = "API Update Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $model['group']['startDate'] = self::$today->format(DATE_ATOM);
      $model['group']['isOpen'] = "true";
      $model['group']['isPublic'] = "true";
      $model['group']['hasChildcare'] = "true";
      $model['group']['isSearchable'] = "true";
      $model['group']['groupType']['@id'] = $groupTypeId;
      $model['group']['timeZone']['@id'] = "115";
      $r = self::$f1->put($model, '/groups/v1/groups/'.$groupId.'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

    /**
    * @group Categories
    */
    public function testCategoryList()
    {
      $r = self::$f1->get('/groups/v1/categories.json');
      $category = $r['body']['categories']['category'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($category, "No group category id returned");
      return $category; 
    }

    
    /**
     * @group Categories
     * @depends testCategoryList
     */
    public function testCategoryShow($categoryId)
    {
     $r = self::$f1->get('/groups/v1/categories/'.$categoryId .'.json');
     $this->assertEquals('200', $r['http_code']);
     $this->assertNotEmpty($r['body'], "No Response Body");
    }



    /**
     * @group Members
     * @depends testGroupList
     */
    public function testMemberSearch($groupId)
    {
      $r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/search.json?membertypeid=1');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response");
    }


    /**
     * @group Members
     * @depends testGroupList
     */
    //public function testMemberList($groupId)
    //{
      //$r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members.json');
      //$memberId = $r['body']['members']['member'][0]['@id'];
      //$this->assertEquals('200', $r['http_code']);
      //$this->assertNotEmpty($memberId, "No Member ID");
      //return $memberId;
    //}
	
	//Overwite by grace zhang testcase testMemberList:find a group which has valid member
	/**
     * @group Groups
     * @depends testGroupTypeList
     */
	public function testMemberList($groupTypeId)
    {
	  $r1 = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId.'/groups.json');
      $this->assertEquals('200', $r1['http_code']);  
	  $groupIdCount = count($r1['body']['groups']['group']);	  
	  print_r("groupIdCount =");	
	  print_r($groupIdCount);
	  
	  for ($i = 0; $i < $groupIdCount; $i++) 
	  {
		$groupId = $r1['body']['groups']['group'][$i]['@id'];
	    $r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members.json');
        $memberId = $r['body']['members']['member'][0]['@id'];
        if($memberId != null)
		{
			$this->assertEquals('200', $r['http_code']);
            $this->assertNotEmpty($memberId, "No Member ID");
            return $memberId;
			break;
		}
	  }		
    }

    /**
     * @group Members
     * @depends testGroupList
     * @depends testMemberList
     */
    //public function testMemberShow($groupId, $memberId)
    //{
      //$r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'.json');  
      //$this->assertEquals('200', $r['http_code']);  
      //$this->assertNotEmpty($r['body'], "No Response Body");  
    //}
	//Overwite by grace zhang testcase testMemberShow:find a group which has valid member
	/**
     * @group Groups
     * @depends testGroupTypeList
     */
	 public function testMemberShow($groupTypeId)
    {
	  $r1 = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId.'/groups.json');
      $this->assertEquals('200', $r1['http_code']);  
	  $groupIdCount = count($r1['body']['groups']['group']);	  
	  print_r("groupIdCount =");	
	  print_r($groupIdCount);
	  
	  for ($i = 0; $i < $groupIdCount; $i++) 
	  {
		$groupId = $r1['body']['groups']['group'][$i]['@id'];
	    $r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members.json');
        $memberId = $r['body']['members']['member'][0]['@id'];
        if($memberId != null)
		{
			$r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'.json');  
            $this->assertEquals('200', $r['http_code']);  
            $this->assertNotEmpty($r['body'], "No Response Body");
            break;			
		}
	  }		
      
    }

    /**
     * @group Members
     * @depends testGroupList
     * @depends testMemberList
     */
    //public function testMemberEdit($groupId, $memberId)
    //{
       //$model = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'/edit.json');
       //$this->assertEquals('200', $model['http_code']);
       //$this->assertNotEmpty($model['body'], "No Response Body");
       //return $model['body'];
    //}
	//Overwite by grace zhang testcase testMemberEdit:find a group  which has valid member	
	public function testMemberEdit()
    {
		
	  $r0 = self::$f1->get('/groups/v1/grouptypes.json'); 
      $this->assertEquals('200', $r0['http_code']);     
	  $groupTypeIdCount = count($r0['body']['groupTypes']['groupType']);
      print_r("groupTypeIdCount =");	
	  print_r($groupTypeIdCount);
	  for ($i = 0; $i < $groupTypeIdCount; $i++) 
	  {
	    $groupTypeId = $r0['body']['groupTypes']['groupType'][$i]['@id'];	
        print_r("groupTypeId =");	
	    print_r($groupTypeId);		
	    $r1 = self::$f1->get('/groups/v1/grouptypes/'.$groupTypeId.'/groups.json');
        $this->assertEquals('200', $r1['http_code']);  
	    $groupIdCount = count($r1['body']['groups']['group']);	  
	    print_r("groupIdCount =");	
	    print_r($groupIdCount);
	    
		$index =0;
	    for ($j = 0; $j < $groupIdCount; $j++) 
	    {
		  $groupId = $r1['body']['groups']['group'][$j]['@id'];
	      $r = self::$f1->get('/groups/v1/groups/'.$groupId.'/members.json');
          $memberId = $r['body']['members']['member'][0]['@id'];
          if($memberId != null)
		  {
			  $model = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'/edit.json');
              $this->assertEquals('200', $model['http_code']);
              $this->assertNotEmpty($model['body'], "No Response Body");
              return $model['body'];
			  print_r("testMemberEdit - model=");
		      print_r($model['body']);
			  //break;
		  }
		  $index++;
	    }
		print_r("index =");	
	    print_r($index);
		if($index==$groupIdCount)
		{
			continue;
		}
		else
		{
			 break;
		}
	  }		
       
    }

   /**
     * @group Members
     * @depends testGroupList
     */
    public function testMemberNew($groupId)
    {
      $model = self::$f1->get('/groups/v1/groups/'.$groupId.'/members/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }

    /**
     * @group Members
     * @depends testGroupList
     * @depends testMemberNew
     */
    public function testMemberCreate($groupId, $model)
    {
      $p = self::$f1->get('/v1/people/search.json?searchfor="steve"');
      $personId = $p['body']['results']['person'][0]['@id'];
      $model['member']['person']['@id'] = $personId;
      $model['member']['memberType']['@id'] = "1";
      $model['member']['group']['@id'] = $groupId;      
      $r = self::$f1->post($model, '/groups/v1/groups/'.$groupId.'/members.json');
      $memberId = $r['body']['member']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($memberId, "No Response Body");
      return $memberId;
    }

    /**
     * @group Members
     * @depends testGroupList
     * @depends testMemberCreate
     * @depends testMemberEdit
     */
    public function testMemberUpdate($groupId, $memberId, $model)
    {
      $p = self::$f1->get('/v1/people/search.json?searchfor="john"');
      $personId = $p['body']['results']['person'][0]['@id'];
      $model['member']['person']['@id'] = $personId;
      $model['member']['memberType']['@id'] = "2";
      $model['member']['group']['@id'] = $groupId;  
	  print_r("model =");
	  print_r($model);
      $r = self::$f1->put($model, '/groups/v1/groups/'.$groupId.'/members/'.$memberId.'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

    /**
     * @group Members
     * @depends testGroupList
     * @depends testMemberCreate
     */
    public function testMemberDelete($groupId, $memberId)
    {
      $r = self::$f1->delete('/groups/v1/groups/'.$groupId.'/members/'.$memberId.'.json');
      $this->assertEquals('204', $r['http_code']);    
      $this->assertEmpty($r['body'], 'Failed to delete resource');
    }


    /**
     * @group MemberTypes
     */
    public function testMemberTypeList()
    {
      $r = self::$f1->get('/groups/v1/membertypes.json');
      $memberTypeId = $r['body']['memberTypes']['memberType'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($memberTypeId, "No Member ID");
      return $memberTypeId;
    }

    /**
     * @group MemberTypes
     * @depends testMemberTypeList
     */
    public function testMemberTypeShow($memberTypeId)
    {
      $r = self::$f1->get('/groups/v1/membertypes/'.$memberTypeId.'.json'); 
      $this->assertEquals('200', $r['http_code']);   
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }


    /**
     * @group Prospects
     */
    public function testProspectNew()
    {
      $model = self::$f1->get('/groups/v1/prospects/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body"); 
      return $model['body'];
    }

    /**
     * @group Prospects
     * @depends testGroupList
     * @depends testProspectNew
     */
    public function testProspectCreate($groupId, $model)
    {
      $model['prospect']['firstName'] = "Test";
      $model['prospect']['lastName'] = "Prospect";
      $model['prospect']['email'] = self::$today->format("Y-m-d H:i:s")."@email.com";
      $r = self::$f1->post($model, '/groups/v1/groups/'.$groupId.'/prospects.json');
      $prospectId = $r['body']['prospect']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($prospectId, "No Response Body");
      return $prospectId;
    }

    
     /**
     * @group DateRangeTypes
     */
    public function testDateRangeTypeList()
    {
      $r = self::$f1->get('/groups/v1/daterangetypes.json');
      $dateRangeTypeId = $r['body']['dateRangeTypes']['dateRangeType'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotNull($dateRangeTypeId, "No Date Range Type ID");
      return $dateRangeTypeId;
    }

      
    /**
     * @group DateRangeTypes
     * @depends testDateRangeTypeList
     */
    public function testDateRangeTypeShow($dateRangeTypeId)
    {
      $r = self::$f1->get('/groups/v1/daterangetypes/'.$dateRangeTypeId.'.json');
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotNull($r['body'], "No response body");
      
    }

    /**
     * @group Genders
     */
    public function testGenderList()
    {
      $r = self::$f1->get('/groups/v1/genders.json');
      $genderId = $r['body']['genders']['gender'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotNull($r['body'], "No Response Body");
      return $genderId;  
    }


    /**
     * @group Genders
     * @depends testGenderList
     */
    public function testGenderShow($genderId)
    {
      $r = self::$f1->get('/groups/v1/genders/'.$genderId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }
 
    /**
     * @group MaritalStatuses
     */
    public function testMaritalStatusList()
    {
      $r = self::$f1->get('/groups/v1/maritalstatuses.json');
      $maritalStatusId = $r['body']['maritalStatuses']['maritalStatus'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotNull($maritalStatusId, "No Marital Status ID");
      return $maritalStatusId;
    }

    /**
     * @group MaritalStatuses
     * @depends testMaritalStatusList
     */
    public function testMaritalStatusShow($maritalStatusId)
    {
      $r = self::$f1->get('/groups/v1/maritalstatuses/'.$maritalStatusId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }

 
   /**
     * @group TimeZone
     */
    public function testTimeZoneList()
    {
      $r = self::$f1->get('/groups/v1/timezones.json');
      $timeZoneId = $r['body']['timezones']['timezone'][0]['@id'];
      $this->assertEquals('200', $r['http_code']);
      $this->assertNotEmpty($timeZoneId, "No TimeZone ID");
      return $timeZoneId;
    }

    /**
     * @group TimeZone
     * @depends testTimeZoneList
     */
    public function testTimeZoneShow($timeZoneId)
    {
      $r = self::$f1->get('/groups/v1/timezones/'.$timeZoneId.'.json');
      $this->assertEquals('200', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }    
}
?>