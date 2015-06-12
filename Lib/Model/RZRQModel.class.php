<?php
class RZRQModel extends RelationModel{

	protected $tableName = 'rzrq_summary';

	public function _initialize(){
	}

	//单位 亿元
	public function getSummaryIncreaseData($field,$market)
	{

		$rzrqSummary = D("rzrqSummary");
		//$sql = 'SELECT m1.date,m1.'.$field.'/10000 as '.$field.',COALESCE(m1.'.$field.' - (SELECT m2.'.$field.' FROM rzrq_summary m2 WHERE m2.id = m1.id - 1 and market='.$market.'), 0)/10000 AS increase FROM rzrq_summary m1 where market='.$market.' order by m1.date asc'; 
		$sql = 'SELECT '.$field.'/10000 as '.$field.',date FROM rzrq_summary WHERE market='.$market.' GROUP BY date ORDER BY date asc';
        $data = $rzrqSummary->query($sql);

        for($i=1;$i<count($data);$i++){
			$data[$i]['increase'] = $data[$i][$field] -$data[$i-1][$field];
        }
        return $data;

	}

	public function getStockIncreaseData($field,$stockSymbol)
	{
        $rzrqSummary = D("rzrq_each_stock");
		$sql = 'SELECT m1.date,m1.'.$field.',COALESCE(m1.'.$field.' - (SELECT m2.'.$field.' FROM rzrq_summary m2 WHERE m2.id = m1.id - 1 and stock_id='.$stockSymbol.'), 0) AS increase FROM rzrq_summary m1 where stock_id='.$stockSymbol.' order by m1.date asc'; 
        $data = $rzrqSummary->query($sql);
        return $data;

	}

	public function chkAddRZRQStockSymbol($symbol,$name,$market)
	{
		$stock_id = D('StockInfo')->getIDFromSymbol($symbol);
		$market = ($maret ==1)? "ShangHaiA":"ShenZhenA";
		$assertModel = D('asset_info');
		if ($stock_id == ''){
			$assertModel->Add(array('symbol'=>$symbol,'name'=>$name,'market'=>$market,'type'=>5,'currency'=>1));
		}

		$stock_id = D('StockInfo')->getIDFromSymbol($symbol);

		return $stock_id;
	}

	public function makeSummaryChart($market,$chartTitle,&$chartData)
	{
		$indexChartModel = D("IndexChart");
		$data =  $this->getSummaryIncreaseData("today_rz_sum",$market);
		$graph = array(
            0=>array('valueField'=>'today_rz_sum','title'=>'融资总量','type' => 'line','position' => 'left'),
            1=>array('valueField'=>'increase','title'=>'融资增量','type' => 'column','position' => 'right'),
            2=>array('valueField'=>'quote','title'=>'走势','type' => 'line','position' => 'right')
        );
        
        $stockModel =  D('StockQuoteHistory');

		if ($market==Market_ShangHai)
			$stock_id = $this->chkAddRZRQStockSymbol("000001.SS",'上证指数',Market_ShangHai);
		else
			$stock_id = $this->chkAddRZRQStockSymbol("399001.SZ",'深圳成指',Market_ShenZhen);


        $quote = $stockModel->getQuoteFromDateWithID($stock_id,$data[0]['date']);
        foreach ($data as &$value) {
            if (!isset($quote[$value['date']])){
                $value['quote'] = 0;
            }
            else $value['quote'] = $quote[$value['date']];
        }


        $indexChartModel->newChart(
            $chartTitle,ChartType_mutilMixedLineColumn,$data,'date',$graph,$chartData,"morethan",0,1000);

        return $chartData;

	}
	public function makeStockChart()
	{

	}



}
