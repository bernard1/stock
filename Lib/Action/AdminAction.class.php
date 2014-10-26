<?php

/*********************************************************#

#*********************************************************/
class AdminAction extends Action {

	public function _initialize(){
        include_once "Include/define.php";
        include_once "Include/functions.php";
        include_once "Include/class.yahoostock.php";
	}
	public function index(){



        //$objYahooStock->addFormat("snl1d1t1cvj1m3");
        //$objYahooStock->addStock("0700.HK");
 
        /**
         * Printing out the data
         */
        foreach( $objYahooStock->getQuotes() as $code => $stock)
        {
        	echo "<br>";
        	print_r($stock);
        	echo "<br>";
        }		
	}
    public function test()
    {
        $qhModel = D('Positions');
        MYDUMP($qhModel->updateMarketValue());
    }
}


