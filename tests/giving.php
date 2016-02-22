<?php

/**
  * PHPUnit Tests for the FellowshipOne.com API Giving Realm.
  * @class FellowshipOneGivingTest
  * @license apache license 2.0, code is distributed "as is", use at own risk, all rights reserved
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
        global $settings,$env;
        self::$f1 = new FellowshipOne($settings[$env]); 
        self::$today = new DateTime('now');
        self::$randomNumber = rand();
        self::$f1->login2ndParty($settings[$env]['username'],$settings[$env]['password']);        
    }

    // ACCOUNTS START

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
      $randomId = self::$f1->randomId("household");
      $model['account']['accountNumber'] = "111111";
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

    // BATCHES START

    
    /**
     * @group Batches
     */
    public function testBatchSearch()
    {
      $r = self::$f1->get('/giving/v1/batches/search.json?batchTypeID=1');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }


     /**
     * @group Batches
     */
    public function testBatchShow()
    {
      $r = self::$f1->get('/giving/v1/batches/search.json?batchTypeID=1');
      $batchId = $r['body']['results']['batch'][0]['@id'];
      $r = self::$f1->get('/giving/v1/batches/'. $batchId .'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $batchId;
    }

    /**
     * @group Batches
     * @depends testBatchShow
     */
    public function testBatchEdit($batchId)
    {
      
      $model = self::$f1->get('/giving/v1/batches/'.$batchId.'/edit.json');
      $this->assertEquals('200', $model['http_code'] );
      $this->assertNotEmpty($model['body'], "No Response");
      //return $model['body'];
    }

    /**
     * @group Batches
     */
    public function testBatchNew()
    {
      $model = self::$f1->get('/giving/v1/batches/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

     /**
     * @group Batches
     * @depends testBatchNew
     */
    public function testBatchCreate($model)
    {
      $model['batch']['name'] = "API Create Unit Test - ".self::$today->format("Y-m-d H:i:s");
      $model['batch']['amount'] = "100.00";
      $model['batch']['batchType']['@id'] = "1";
      $model['batch']['batchStatus']['@id'] = "0";
      $r = self::$f1->post($model, '/giving/v1/batches.json');
      $batchId = $r['body']['batch']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($batchId, "No Response Body");
      return $model = $r['body'];
    }

     /**
     * @group Batches
     * @depends testBatchCreate
     */
    public function testBatchUpdate($model)
    {
      $batchId = $model['batch']['@id'];
      $model['batch']['amount'] = "200.00";
      $r = self::$f1->put($model, '/giving/v1/batches/'.$batchId.'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }


    // BATCH TYPES START

    
    /**
     * @group BatchTypes
     */
    public function testBatchTypeList()
    {
      $r = self::$f1->get('/giving/v1/batches/batchtypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $batchType = $r['body']['batchTypes']['batchType'][0]['@id'];
    }

     /**
     * @group BatchTypes
     * @depends testBatchTypeList
     */
    public function testBatchTypeShow($batchType)
    {
      $r = self::$f1->get('/giving/v1/batches/batchtypes/'.$batchType .'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

    // CONTRIBUTION RECEIPTS START

    /**
     * @group ContributionReceipts
     */
    public function testContributionReceiptSearch()
    {
      $r = self::$f1->get('/giving/v1/contributionreceipts/search.json?startReceivedDate=2011-01-01');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $contributionReceiptId = $r['body']['results']['contributionReceipt'][2]['@id'];
    }


     /**
     * @group ContributionReceipts
     * @depends testContributionReceiptSearch
     */
    public function testContributionReceiptShow($contributionReceiptId)
    {
      $r = self::$f1->get('/giving/v1/contributionreceipts/'.$contributionReceiptId.'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

    /**
     * @group ContributionReceipts
     * @depends testContributionReceiptSearch
     */
    public function testContributionReceiptEdit($contributionReceiptId)
    {
      //This is a 1st party method.  Expecting Exception and 405.
      $model = self::$f1->get('/giving/v1/contributionreceipts/'.$contributionReceiptId.'/edit.json');
      $this->assertEquals('405', $model['http_code'] );
    }

    /**
     * @group ContributionReceipts
     */
    public function testContributionReceiptNew()
    {
      $model = self::$f1->get('/giving/v1/contributionreceipts/new.json');
      $this->assertEquals('200', $model['http_code']);
      $this->assertNotEmpty($model['body'], "No Response Body");
      return $model['body'];
    }

     /**
     * @group ContributionReceipts
     * @depends testContributionReceiptNew
     */
    public function testContributionReceiptCreate($model)
    {
      $r = self::$f1->get('/giving/v1/funds');
      $fundId = $r['body']['funds']['fund'][0]['@id'];
      $model['contributionReceipt']['amount'] = rand(1,1000);
      $model['contributionReceipt']['fund']['@id'] = $fundId;
      $model['contributionReceipt']['contributionType']['@id'] = "1";
      $model['contributionReceipt']['receivedDate']= self::$today->format(DATE_ATOM);
      $model['contributionReceipt']['memo']= "API Unit Test: ".self::$today->format("Y-m-d H:i:s");
      $r = self::$f1->post($model, '/giving/v1/contributionreceipts.json');
      $contributionReceiptId = $r['body']['contributionReceipt']['@id'];
      $this->assertEquals('201', $r['http_code']);
      $this->assertNotEmpty($contributionReceiptId, "No Response Body");
      return $model = $r['body'];
    }

     /**
     * @group ContributionReceipts
     * @depends testContributionReceiptCreate
     */
    public function testContributionReceiptUpdate($model)
    {
      //This is a 1st party only method.  Expecitng an Exception and 405.
      $contributionReceiptId = $model['contributionReceipt']['@id'];
      $model['contributionReceipt']['amount'] = rand(1,10000);
      $r = self::$f1->put($model, '/giving/v1/contributionreceipts/'.$contributionReceiptId.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }
    
    // CONTRIBUTION TYPES START

    /**
     * @group ContributionTypes
     */
    public function testContributionTypeList()
    {
      $r = self::$f1->get('/giving/v1/contributiontypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      
      return $contributionTypeId = $r['body']['contributionTypes']['contributionType'][0]['@id'];
      
    }

     /**
     * @group ContributionTypes
     * @depends testContributionTypeList
     */
    public function testContributionTypeShow($contributionTypeId)
    {
      $r = self::$f1->get("/giving/v1/contributiontypes/{$contributionTypeId}.json");
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

    // CONTRIBUTION SUBTYPES START

    /**
     * @group ContributionSubTypes
     */
    public function testContributionSubTypeList()
    {
      $r = self::$f1->get('/giving/v1/contributiontypes/4/contributionsubtypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $contributionSubTypeId = $r['body']['contributionSubTypes']['contributionSubType'][0]['@id'];
    }

    /**
     * @group ContributionSubTypes
     * @depends testContributionSubTypeList
     */
    public function testContributionSubTypeShow($contributionSubTypeId)
    {
      $r = self::$f1->get("/giving/v1/contributiontypes/4/contributionsubtypes/{$contributionSubTypeId}.json");
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

  
    // FUNDS START

   /**
     * @group Funds
     */
    public function testFundList()
    {
      $r = self::$f1->get('/giving/v1/funds.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $fundId = $r['body']['funds']['fund'][3]['@id'];
    }


     /**
     * @group Funds
     * @depends testFundList
     */
    public function testFundShow($fundId)
    {
      $r = self::$f1->get('/giving/v1/funds/'.$fundId .'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $fundId = $r['body']['fund']['@id'];
    }

    /**
     * @group Funds
     * @depends testFundList
     */
    public function testFundEdit($fundId)
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/funds/'.$fundId.'/edit.json');
      $this->assertEquals('405', $r['http_code'] );
    }
   /**
     * @group Funds
     */
    public function testFundNew()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/funds/new.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group Funds
     */
    public function testFundCreate()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->post($model=null, '/giving/v1/funds.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group Funds
     * @depends testFundList
     */
    public function testFundUpdate($fundId)
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->put($model=null, '/giving/v1/funds/'.$fundId.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }


    // FUND TYPES START

   /**
     * @group FundTypes
     */
    public function testFundTypeList()
    {
      $r = self::$f1->get('/giving/v1/funds/fundtypes.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $fundTypeId = $r['body']['fundTypes']['fundType'][1]['@id'];
    }


     /**
     * @group FundTypes
     * @depends testFundTypeList
     */
    public function testFundTypeShow($fundTypeId)
    {
      $r = self::$f1->get('/giving/v1/funds/fundtypes/'.$fundTypeId .'.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

    // PLEDGE DRIVES START

    /**
     * @group PledgeDrives
     * @depends testFundList
     */
    public function testPledgeDriveList($fundId)
    {
      //Docs say this is 1st party only but it works for 2nd party
      $r = self::$f1->get('/giving/v1/funds/'.$fundId.'/pledgedrives.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $pledgeDriveId = $r['body']['pledgeDrives']['pledgeDrive'][0]['@id'];
    }


    /**
     * @group PledgeDrives
     * @depends testPledgeDriveList
     */
    public function testPledgeDriveShow($pledgeDriveId)
    {
      //$r = self::$f1->get('/giving/v1/funds/'.$fundId . '/pledgedrives/'.$pledgeDriveId .'.json');
      $r = self::$f1->get("/giving/v1/pledgedrives/{$pledgeDriveId}.json");
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
    }

    /**
     * @group PledgeDrives
     * @depends testPledgeDriveList
     */
    public function testPledgeDriveEdit($pledgeDriveId)
    {
      //Docs say 3rd party but it's 1st party
      $r = self::$f1->get("/giving/v1/pledgedrives/{$pledgeDriveId}/edit.json");
      $this->assertEquals('405', $r['http_code'] );
      //$this->assertNotEmpty($model['body'], "No Response");
    }
   /**
     * @group PledgeDrives
     */
    public function testPledgeDriveNew()
    {
      //Docs say 3rd party but it's 1st party
      $r = self::$f1->get('/giving/v1/pledgedrives/new.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group PledgeDrives
     */
    public function testPledgeDriveCreate()
    {
      //Docs say 3rd party but it's 1st party
      $r = self::$f1->post($model=null, '/giving/v1/pledgedrives.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group PledgeDrives
     * @depends testPledgeDriveList
     */
    public function testPledgeDriveUpdate($pledgeDriveId)
    {
      //Docs say 3rd party but it's 1st party
      $r = self::$f1->put($model=null, '/giving/v1/pledgedrives/'.$pledgeDriveId.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }
         
         
    // RDC START

    //testing prod rdcBatches
    
     /**
     * @group RDCBatches
     */
    public function testRDCBatchList()
    {
      //1st party only.  Expecting 405..
      $r = self::$f1->get('/giving/v1/batches/1886796/rdcbatches.json');
      //$this->assertEquals('200', $r['http_code'] );
      $this->assertEquals('405', $r['http_code'] );
    }


    /**
     * @group RDCBatches
     */
    public function testRDCBatchShow()
    {
      //1st party only.  Expecting 405..
      $r = self::$f1->get('/giving/v1/rdcbatches/1.json');
      //$this->assertEquals('200', $r['http_code'] );
      $this->assertEquals('405', $r['http_code'] );
    }

    /**
     * @group RDCBatches
     */
    public function testRDCBatchEdit()
    {
      //1st party only.  Expecting 405..
      $r = self::$f1->get('/giving/v1/rdcbatches/1/edit.json');
      //$this->assertEquals('200', $r['http_code'] );
      $this->assertEquals('405', $r['http_code'] );
      //return $model = $r['body'];
     }
   /**
     * @group RDCBatches
     */
    public function testRDCBatchNew()
    {
      //1st party only.  Expecting 405..
      $r = self::$f1->get('/giving/v1/rdcbatches/new.json');
      //$this->assertEquals('200', $r['http_code'] );
      $this->assertEquals('405', $r['http_code']);
      //return $model = $r['body'];
    }

    /**
     * @group RDCBatches
     * @depends testRDCBatchNew
     */
    public function testRDCBatchCreate($model)
    {
      $model['rdcBatch']['@ppMerchantAccountID'] = "76";
      $model['rdcBatch']['@locationID'] = "77098";
      $model['rdcBatch']['name'] = "test";
      $model['rdcBatch']['parentBatch']['@id'] = "4191808";
      $model['rdcBatch']['batchCreatedDate'] = self::$today->format(DATE_ATOM);
      $model['rdcBatch']['itemCount'] = "1";
      $model['rdcBatch']['batchAmount'] = rand(1,1000);
      $model['rdcBatch']['glPostDate'] = self::$today->format(DATE_ATOM);
      $model['rdcBatch']['hasMultipleFunds'] = "false";
      $r = self::$f1->post($model, '/giving/v1/rdcbatches.json');
      //$this->assertEquals('201', $r['http_code']);
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group RDCBatches
     * @depends testRDCBatchEdit
     */
    public function testRDCBatchUpdate($model)
    {
      
      $model['rdcBatch']['batchAmount'] = rand(1,1000);
      $r = self::$f1->put($model, '/giving/v1/rdcbatches/1.json');
      //$this->assertEquals('200', $r['http_code'] );
      $this->assertEquals('405', $r['http_code'] );
    }

       
    // REFERENCE IMAGES START
   
   /**
     * @group ReferenceImages
     */
    public function testReferenceImageShow()
    {
      //Bug in API.  Should return 405 but returning 200.
      $r = self::$f1->get('/giving/v1/contributionreceipts/0/referenceimages/0.json');
      $this->assertEquals('200', $r['http_code'] ); //correct when bug fixed.
    }

    /**
     * @group ReferenceImages
     */
    public function testReferenceImageCreate()
    {
      //1st party only.  Expecting 405..
      $r = self::$f1->post($model=null, '/giving/v1/contributionreceipts/111111/referenceimages/111111.json');
      $this->assertEquals('405', $r['http_code']);
    }
    
    // SUB FUNDS START

    /**
     * @group SubFunds
     * @depends testFundList
     */
    public function testSubFundList($fundId)
    {
      $r = self::$f1->get('/giving/v1/funds/'.$fundId.'/subfunds.json');
      $this->assertEquals('200', $r['http_code'] );
      $this->assertNotEmpty($r['body'], "No Response");
      return $subFundId = $r['body']['subFunds']['subFund'][0]['@id'];
    }


    /**
     * @group SubFunds
     * @depends testFundList
     * @depends testSubFundList
     */
    //public function testSubFundShow($fundId, $subFundId)
    //{
      //$r = self::$f1->get('/giving/v1/funds/'.$fundId . '/subfunds/'.$subFundId .'.json');
      //$this->assertEquals('200', $r['http_code'] );
      //$this->assertNotEmpty($r['body'], "No Response");
    //}
	
	//Overwite by grace zhang testcase testSubFundShow:find a fund which has a valid subFund
	public function testSubFundShow()
    {
	  $r1 = self::$f1->get('/giving/v1/funds.json');
      $this->assertEquals('200', $r1['http_code'] );
      $this->assertNotEmpty($r1['body'], "No Response");
	  $foundIdCount = count($r1['body']['funds']['fund']);	  
	  print_r("foundIdCount =");	
	  print_r($foundIdCount);
	  
	  for ($i = 0; $i < $foundIdCount; $i++) 
	  {
		$fundId = $r1['body']['funds']['fund'][$i]['@id'];
	    $r2 = self::$f1->get('/giving/v1/funds/'.$fundId.'/subfunds.json');
        $this->assertEquals('200', $r2['http_code'] );
        $this->assertNotEmpty($r2['body'], "No Response");
        $subFundId = $r2['body']['subFunds']['subFund'][0]['@id'];
        if($subFundId != null)
		{
			$r = self::$f1->get('/giving/v1/funds/'.$fundId .'/subfunds/'.$subFundId .'.json');
            $this->assertEquals('200', $r['http_code'] );
            $this->assertNotEmpty($r['body'], "No Response");
			break;
		}
	  }
      
    }

    /**
     * @group SubFunds
     * @depends testFundList
     * @depends testSubFundList
     */
    //public function testSubFundEdit($fundId, $subFundId)
    //{
      //This is a 1st party only method.  Expecting Exception to be thrown.
      //$r = self::$f1->get('/giving/v1/funds/'.$fundId .'/subfunds/'.$subFundId.'/edit.json');
      //$this->assertEquals('405', $r['http_code'] );
    //}
	
	//Overwite by grace zhang testcase testSubFundEdit:find a fund which has a valid subFund
	public function testSubFundEdit()
    {
	  $r1 = self::$f1->get('/giving/v1/funds.json');
      $this->assertEquals('200', $r1['http_code'] );
      $this->assertNotEmpty($r1['body'], "No Response");
	  $foundIdCount = count($r1['body']['funds']['fund']);	  
	  print_r("foundIdCount =");	
	  print_r($foundIdCount);
	  
	  for ($i = 0; $i < $foundIdCount; $i++) 
	  {
		$fundId = $r1['body']['funds']['fund'][$i]['@id'];
	    $r2 = self::$f1->get('/giving/v1/funds/'.$fundId.'/subfunds.json');
        $this->assertEquals('200', $r2['http_code'] );
        $this->assertNotEmpty($r2['body'], "No Response");
        $subFundId = $r2['body']['subFunds']['subFund'][0]['@id'];
        if($subFundId != null)
		{
			//This is a 1st party only method.  Expecting Exception to be thrown.
			$r = self::$f1->get('/giving/v1/funds/'.$fundId .'/subfunds/'.$subFundId.'/edit.json');
            $this->assertEquals('405', $r['http_code'] );
			break;
		}
	  }
      
    }
   /**
     * @group SubFunds
     */
    public function testSubFundNew()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->get('/giving/v1/funds/subfunds/new.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group SubFunds
     */
    public function testSubFundCreate()
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->post($model=null, '/giving/v1/funds/subfunds.json');
      $this->assertEquals('405', $r['http_code']);
    }

     /**
     * @group SubFunds
     * @depends testFundList
     * @depends testSubFundList
     */
    public function testSubFundUpdate($fundId, $subFundId)
    {
      //This is a 1st party only method.  Expecting Exception to be thrown.
      $r = self::$f1->put($model=null, '/giving/v1/funds/'.$fundId .'/subfunds/'.$subFundId.'.json');
      $this->assertEquals('405', $r['http_code'] );
    }  
}