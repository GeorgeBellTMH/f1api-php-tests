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
    protected static $randomNumber;
    
    public static function setupBeforeClass()
    {
        global $settings;
        $env = 'staging';
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$randomNumber = rand();
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']);        
    }

    /**
     * @group Accounts
     */
    public function testAccountSearch()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/accounts/search.json?peopleID='.self::$randomNumber);
      $this->assertEquals('405', $r['http_code'] );
    }


    /**
     * @group Accounts
     */
    public function testAccountShow()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/accounts/'.self::$randomNumber.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }

    /**
     * @group Accounts
     */
    public function testAccountEdit()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/accounts/'.self::$randomNumber.'/edit.json');
      $this->assertEquals('405', $r['http_code'] );
    }

    /**
     * @group Accounts
     */
    public function testAccountNew()
    {
      $model = self::$f1->get('/giving/v1/accounts/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

     /**
     * @group Accounts
     * @depends testAccountNew
     */
    public function testAccountCreate($model)
    {
      $randomId = self::$f1->randomId();
      $model['account']['accountNumber'] = self::$randomNumber;
      $model['account']['household']['@id'] = $randomId['household'];
      $model['account']['accountType']['@id'] = "1";
      $r = self::$f1->post($model, '/giving/v1/accounts.json');
      $accountId = $r['body']['account']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($accountId, "No Response Body");
      return $accountId;
    }

     /**
     * @group Accounts
     * @depends testAccountCreate
     */
    public function testAccountUpdate($accountId)
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->put(null, '/giving/v1/accounts/'.$accountId.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }

    /**
     * @group AccountTypes
     */
    public function testAccountTypeList()
    {
      $r = self::$f1->get('/giving/v1/accounts/accounttypes.json');
      $this->assertEquals('200', $r['http_code'] );
    }
   
    /**
     * @group AccountTypes
     */
    public function testAccountTypeShow()
    {
      $r = self::$f1->get('/giving/v1/accounts/accounttypes/1.json');
      $this->assertEquals('200', $r['http_code'] );
    }

    // Todo ...
    // Batches

    // Batches: Search (3rd)| Show (3rd)| Edit (3rd)| New (3rd)| Create (3rd)| Update (3rd)
    // Batch Types: List (3rd)| Show (3rd)
    // Contribution Receipts

    // Contribution Receipts: Search (3rd)| Show (3rd)| Edit (1st)| New (3rd)| Create (3rd)| Update (1st)
    // Contribution Types

    // Contribution Types: List (3rd)| Show (3rd)
    // Funds

    // Funds: List (3rd)| Show (3rd)| Edit (1st)| New (1st)| Create (1st)| Update (1st)
    // Fund Types: List (3rd)| Show (3rd)
    // Pledge Drives

    // Pledge Drives: List (1st)| Show (1st)| Edit (3rd)| New (3rd)| Create (3rd)| Update (3rd)
    // Remote Deposit Capture Batches (RDC)

    // RDC Batches: List (1st)| Show (1st)| Edit | New (1st)| Create (1st)| Update (1st)
    // RDC Batch Items: List (1st)| Show (1st)| Edit (1st)| New (1st)| Create (1st)| Update (1st)
    // Reference Images

    // Reference Images: Show (1st)| Create (1st)
    // Sub Funds

    // Sub Funds: List (3rd)| Show (3rd)| Edit (1st)| New (1st)| Create (1st)| Update (1st)
   

}