<?php

class sandbox {
    /**
     * @group Accounts
     */
    public function testAccountSearch()
    {
      $r = self::$f1->get('/giving/v1/accounts/search.json?peopleID=11111111');
      $this->assertEquals('HTTP/1.1 200 OK', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response");
    }

    /**
     * @group Accounts
     * @depends testAccountTypeList
     */
    public function testAccountList($groupTypeId)
    {
      $r = self::$f1->get('/giving/v1/grouptypes/'.$groupTypeId.'/groups.json');
      $groupId = $r['body']['groups']['group'][0]['@id'];
      $this->assertEquals('HTTP/1.1 200 OK', $r['http_code']);
      $this->assertNotEmpty($groupId, "No Account ID");
      return $groupId;
    }

    /**
     * @group Accounts
     * @depends testAccountList
     */
    public function testAccountShow($groupId)
    {
      $r = self::$f1->get('/giving/v1/groups/'.$groupId.'.json');
      $this->assertEquals('HTTP/1.1 200 OK', $r['http_code']);    
      $this->assertNotEmpty($r['body'], "No Response Body");  
    }

    /**
     * @group Accounts
     * @depends testAccountList
     */
    public function testAccountEdit($groupId)
    {
       $model = self::$f1->get('/giving/v1/groups/'.$groupId.'/edit.json');
       $this->assertEquals('HTTP/1.1 200 OK', $model['http_code']);
       $this->assertNotEmpty($model['body'], "No Response Body");
       return $model['body'];
    }

   /**
     * @group Accounts
     */
    public function testAccountNew()
    {
      $model = self::$f1->get('/giving/v1/groups/new.json');
      $this->assertEquals('HTTP/1.1 200 OK', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

    /**
     * @group Accounts
     * @depends testAccountNew
     * @depends testAccountTypeList
     */
    public function testAccountCreate($model, $groupTypeId)
    {
      $model['group']['name'] = "API Create Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $model['group']['startDate'] = self::$today->format(DATE_ATOM);
      $model['group']['isOpen'] = "true";
      $model['group']['isPublic'] = "true";
      $model['group']['hasChildcare'] = "true";
      $model['group']['isSearchable'] = "true";
      $model['group']['groupType']['@id'] = $groupTypeId;
      $model['group']['timeZone']['@id'] = "115";
      $r = self::$f1->post($model, '/giving/v1/groups.json');
      $groupId = $r['body']['group']['@id'];
      $this->assertEquals('HTTP/1.1 201 Created', $r['http_code']);
      $this->assertNotEmpty($groupId, "No Response Body");
      return $groupId;
    }

    /**
     * @group Accounts
     * @depends testAccountCreate
     * @depends testAccountTypeList
     * @depends testAccountEdit
     */
    public function testAccountUpdate($groupId, $groupTypeId, $model)
    {
      $model['group']['name'] = "API Update Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $model['group']['startDate'] = self::$today->format(DATE_ATOM);
      $model['group']['isOpen'] = "true";
      $model['group']['isPublic'] = "true";
      $model['group']['hasChildcare'] = "true";
      $model['group']['isSearchable'] = "true";
      $model['group']['groupType']['@id'] = $groupTypeId;
      $model['group']['timeZone']['@id'] = "115";
      $r = self::$f1->put($model, '/giving/v1/groups/'.$groupId.'.json');
      $this->assertEquals('HTTP/1.1 200 OK', $r['http_code']);
      $this->assertNotEmpty($r['body'], "No Response Body"); 
    }

}
