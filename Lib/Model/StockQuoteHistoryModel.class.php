<?php
class StockQuoteHistoryModel extends RelationModel{
	//protected $_name = 'stock_quote_history';




	//check newest price history and save to database.
	public function updateStockQuote($stock_id)
	{
		include_once "Include/define.php";
        include_once "Include/functions.php";
        include_once "Include/class.yahoostock.php";
        $lastQHDate = $this->field('max(date) as maxDate')->where('stock_id='.$stock_id)->select();

        //one month ago
		if (empty($lastQHDate[0]['maxDate'])){  
            $beginDate = DateAdd('d',-30,now());
        }
        else{
        	//already newset,not need update
        	if (DateDiff('d',$lastQHDate[0]['maxDate'],now())<=1)	return false;

        	$beginDate = DateAdd('d',1,$lastQHDate[0]['maxDate']);
        }
        $endDate = get_date(now());
        $beginDate = get_date($beginDate);

        $symbol = D('StockInfo')->getSymbolFromID($stock_id);
        $objYahooStock = new YahooStock; 
		$quoteHistory = $objYahooStock->getQuoteHistory($symbol,$beginDate,$endDate);
		if (empty($quoteHistory)) return false;

		foreach ($quoteHistory as $value) {
			$this->add(
				array(
					'stock_id'=>$stock_id,
					'date'=>$value[0],
					'open'=>$value[1],
					'high'=>$value[2],
					'low'=>$value[3],
					'close'=>$value[4],
					'volume'=>$value[5],
					'adj_close'=>$value[6],
				)
			 );
		}
		return true;
	}



	public function getStockQuoteHistory($symbol,$beginDate,$endDate){
		include_once "Include/define.php";
        include_once "Include/functions.php";
        include_once "Include/class.yahoostock.php";

        $objYahooStock = new YahooStock; 
		$quoteHistory = $objYahooStock->getQuoteHistory($symbol,$beginDate,$endDate);

		if (empty($quoteHistory)) return false;
		$arr = array();

		//$data['date'] = price;
		foreach ($quoteHistory as $value) {
			$arr[$value[0]] = $value[4];
		}
		return $arr;
	}


	//get close price with id
	public function getLastStockQuoteWithID($stock_id)
	{
		//check update first
		$this->updateStockQuote($stock_id);

		$quote = $this->order('date desc')->where('stock_id='.$stock_id)->limit(1)->select();
		if (!empty($quote[0]))
			return $quote[0]['close'];
		else{
			
		}

		return 0;
	}

	//get close price with symbol
	public function getLastStockQuoteWithSymbol($symbol)
	{
		$id = D('StockInfo')->getIDFromSymbol($symbol);
		return $this->getLastStockQuoteWithID($id);
	}


	public function getQuoteByDate($stock_id,$date){
		//check update first
		$this->updateStockQuote($stock_id);

		$quote = $this->where('date="'.$date.'" AND stock_id='.$stock_id)->select();
		if (empty($quote[0])){
			$quote = $this->where('stock_id='.$stock_id)->order('date desc')->select();
			return $quote[0]['close'];
		}
		else
			return $quote[0]['close'];

	}
	public function getQuoteFromDateWithSymbol($symbol,$date){

		$stockModel = D('StockInfo');
		$stock_id = $stockModel->getIDFromSymbol($symbol);
		$this->updateStockQuote($stock_id);
		$quote = $this->where('date>="'.$date.'" AND stock_id='.$stock_id)->select();

		$return = array();
		foreach ($quote as $value) {
			$return[$value['date']]=$value['close'];
		}
		return $return;
	}


	public function getLastQuoteFromSina($symbol)
	{
		$url = "http://hq.sinajs.cn/list=".$symbol;
		$s = file_get_contents($url);
        if (empty($s))    return '';
        /**
         * convert the comma separated data into array
         * populate result array with stock code as key
         */
        
        $fields = split(',', $s);
        
        //0：”大秦铁路”，股票名字；
		//1：”27.55″，今日开盘价；
		//2：”27.25″，昨日收盘价；
		//3：”26.91″，当前价格；
		//4：”27.55″，今日最高价；
		//5：”26.20″，今日最低价；
        return $fields[3];
	}
}
