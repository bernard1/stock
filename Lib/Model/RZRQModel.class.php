<?php
class RZRQModel extends RelationModel{

	protected $tableName = 'rzrq_summary';

	public function _initialize(){
	}

	//单位 亿元
	public function getSummaryIncreaseData($field,$market)
	{

		$rzrqSummary = D("rzrqSummary");
		$sql = 'SELECT m1.date,m1.'.$field.'/10000 as '.$field.',COALESCE(m1.'.$field.' - (SELECT m2.'.$field.' FROM rzrq_summary m2 WHERE m2.id = m1.id - 1 and market='.$market.'), 0)/10000 AS increase FROM rzrq_summary m1 where market='.$market.' order by m1.date asc'; 
        $data = $rzrqSummary->query($sql);

        return $data;

	}

	public function getStockIncreaseData($field,$stockSymbol)
	{
        $rzrqSummary = D("rzrq_each_stock");
		$sql = 'SELECT m1.date,m1.'.$field.',COALESCE(m1.'.$field.' - (SELECT m2.'.$field.' FROM rzrq_summary m2 WHERE m2.id = m1.id - 1 and stock_id='.$stockSymbol.'), 0) AS increase FROM rzrq_summary m1 where stock_id='.$stockSymbol.' order by m1.date asc'; 
        $data = $rzrqSummary->query($sql);
        return $data;

	}

	public function makeSummaryChart($market)
	{
		$indexChartModel = D("IndexChart");
		$chartData = array();
		$data =  $this->getSummaryIncreaseData("today_rz_sum",$market);
		$graph = array(
            0=>array('valueField'=>'today_rz_sum','title'=>'融资总量','type' => 'line','position' => 'left'),
            1=>array('valueField'=>'increase','title'=>'融资增量','type' => 'column','position' => 'right'),
            2=>array('valueField'=>'quote','title'=>'走势','type' => 'line','position' => 'right')
        );
        

		if ($market==2)
			$marketSymbol = "399001.SZ";
		else
			$marketSymbol = "000001.SS";


        $stockModel =  D('StockQuoteHistory');

        $quote = $stockModel->getQuoteFromDateWithSymbol('000001.SS');
        foreach ($data as &$value) {
            if (!isset($quote[$value['date']])){
                $value['quote'] = 0;
            }
            else $value['quote'] = $quote[$value['date']];
        }


        $indexChartModel->newChart(
            "上海 指数,融资,每日增量",ChartType_mutilMixedLineColumn,$data,'date',$graph,$chartData,"morethan",0,500);

        return $chartData;

	}
	public function makeStockChart()
	{

	}



}
