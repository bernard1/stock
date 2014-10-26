<?php
class CurrencyModel extends RelationModel{

	public $currencyCode = array(
			0=>'USD',
			1=>'CNY',
			2=>'HKD',
			3=>'GBP',
		);		

	public function _initialize(){
	}


	//conver all to one currency,default at conf CURRENCY_CODE
	//like getRateArray('CNY') get all CNY currency
	function getRateArray($converCode ='')
	{
		$converCode = C("CURRENCY_CODE");
		$currency = array();
	    foreach ($this->currencyCode as $id => $code) {
        	if ($code == C("CURRENCY_CODE")){
        		$currency[$id]=1;
        		continue;
        	}
        	$currency[$id] = $this->currency_rate($code,$converCode);
        }
        return $currency;
    }


	function currency_convert($fromCode,$toCode,$value)
	{
		return $this->currency_rate($fromCode,$toCode)*$value;
	}


	function currency_rate($fromCode,$toCode)
	{
		$row = $this->where("to_code='".$toCode."' AND from_code='".$fromCode."'")->select();
		if ($row){
			return round(($row[0]['rate']),2);
		}
		else{
			 $currency = json_decode(file_get_contents('http://rate-exchange.appspot.com/currency?from=' . $fromCode . '&to=' . $toCode));
			 $row = array('from_code'=>$fromCode,'to_code'=>$toCode,'rate'=>$currency->rate);
			 $this->add($row);
			 return round(($row['rate']),2);
		}
	}
}
