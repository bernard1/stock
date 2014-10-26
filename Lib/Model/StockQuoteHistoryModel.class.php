<?php
class StockQuoteHistoryModel extends RelationModel{
	protected $tableName = 'stock_quote_history';

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

	//get close price with id
	public function getLastStockQuoteWithID($stock_id)
	{
		//check update first
		$this->updateStockQuote($stock_id);

		$quote = $this->order('date desc')->where('stock_id='.$stock_id)->limit(1)->select();
		if (!empty($quote[0]))
			return $quote[0]['close'];

		return 0;
	}

	//get close price with symbol
	public function getLastStockQuoteWithSymbol($symbol)
	{
		$id = D('StockInfo')->getIDFromSymbol($symbol);
		return getLastStockQuote($id);
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
}
