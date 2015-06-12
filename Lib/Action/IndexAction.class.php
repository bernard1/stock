<?php

/*********************************************************#

#*********************************************************/
class IndexAction extends Action {

	public function _initialize(){
        include_once "Include/define.php";
        include_once "Include/functions.php";
        include_once "Include/class.yahoostock.php";

/*
         $objYahooStock = new YahooStock; 
         MYDUMP($objYahooStock->getQuoteHistory("601688.SS"));
         die;*/
         $this->assign('baseUrl',C('TEMPALTE_BASE_URL'));
	}
    public function Index()
    {
        
        $this->showMarketValue();
    }
    public function test(){
        //D('Positions')->updateMarketValue();
        D('Analysis')->opAnalysis();
    }


    public function showMarketValue()
    {
        $this->display("Tpl/Index/MarketValue.html");
        
    }

    //longPosition,多头仓位
    public function showLongPositionDetail()
    {

        $positionModel = D("Positions");
        $this->holdValues =$positionModel->getStocksValuePercentage();
        $allPosition = $positionModel->relation('asset_info')->order('id asc')->select();

        $chartData = array();
        $indexChartModel = D("IndexChart");
        $pieData = $this->holdValues;
        unset($pieData[count($pieData)-1]);  // remove total line

        //value consist percent pie
        $indexChartModel->newChart(
            "longValueConsist",ChartType_pie,$pieData,'symbol',array('stockValue'),$chartData
        );
        //different market pie
        $stockMarket = D("StockInfo")->where('type='.AssetType_Stock)->field('market')->group('market')->select();
        foreach ($stockMarket as &$market) {
            foreach($allPosition as $position){
                if (strtolower($position['asset_info']['market']) == strtolower($market['market'])){
                    $stockValue = $positionModel->getEachStockCostVaue($position['asset_info']['symbol']);
                    if ($stockValue['stockValue']>0)
                        $market['value'] +=$stockValue['stockValue'];
                }
            }
        }
        $indexChartModel->newChart(
            "longMarketConsist",ChartType_pie,$stockMarket,'market',array('value'),$chartData
        );

        $this->assign('chartData',json_encode($chartData));




        $this->display("Tpl/Index/longPositionDetail.html");
    }

    //shortPosition,空头仓位
    public function showShortPositionDetail()
    {
        $positionModel = D("Positions");
        $this->holdValues =$positionModel->getStocksValuePercentage('short');
        $allPosition = $positionModel->relation('asset_info')->order('id asc')->select();

        $chartData = array();
        $indexChartModel = D("IndexChart");
        $pieData = $this->holdValues;
        unset($pieData[count($pieData)-1]);  // remove total line

        //value consist percent pie
        $indexChartModel->newChart(
            "shortValueConsist",ChartType_pie,$pieData,'symbol',array('stockValue'),$chartData,"lessthan",0
        );

        //different market pie
        $stockMarket = D("StockInfo")->where('type='.AssetType_Stock)->field('market')->group('market')->select();
        foreach ($stockMarket as &$market) {
            foreach($allPosition as $position){
                if (strtolower($position['asset_info']['market']) == strtolower($market['market'])){
                    $stockValue = $positionModel->getEachStockCostVaue($position['asset_info']['symbol']);
                    if ($stockValue['stockValue']<0)
                        $market['value'] +=abs($stockValue['stockValue']);
                }
            }
        }

        $indexChartModel->newChart(
            "shortMarketConsist",ChartType_pie,$stockMarket,'market',array('value'),$chartData
        );
        

        $this->assign('chartData',json_encode($chartData));

        $this->display("Tpl/Index/shortPositionDetail.html");       
    }


    public function showStockProfitChart()
    {


        $retInfo=array();
        $retCount = 0;
        $totalCost = 0;
        $totalValue = 0;
        $totalProfit = 0;
        $totalPercent = 0;

        $positionModel = D("Positions");
        $indexChartModel = D("IndexChart");

        $stockCost = $positionModel->getAllPositionCost();

        $allPosition = $positionModel->relation('asset_info')->order('id asc')->select();
        foreach ($allPosition as $position) {

            if ($position['asset_info']['type']==AssetType_Stock){                
                
                $retInfo[$retCount] = $positionModel->getEachStockCostVaue($position['asset_info']['symbol']);
                $pieValueData[$retCount]['symbol'] = $retInfo[$retCount]['symbol'] = $position['asset_info']['symbol'];
                $pieValueData[$retCount]['value'] =  $retInfo[$retCount]['stockValue'];
                if ($retInfo[$retCount]['profit']>0)
                {
                    $pieValueData[$retCount]['profit_win'] = $retInfo[$retCount]['profit'];
                    $pieValueData[$retCount]['profit_lost'] = 0;
                }
                else
                {
                    $pieValueData[$retCount]['profit_lost'] = abs($retInfo[$retCount]['profit']);
                    $pieValueData[$retCount]['profit_win'] = 0;
                }    
                $retCount++;
            }
        }
        $chartData = array();



        //profit_win consist pie
        $indexChartModel->newChart(
            "profitWinConsist",ChartType_pie,$pieValueData,'symbol',array('profit_win'),$chartData,"morethan",5000
        );

        //profit_lost consist pie
        $indexChartModel->newChart(
            "profitLostConsist",ChartType_pie,$pieValueData,'symbol',array('profit_lost'),$chartData,"morethan",0
        );

     
        //end different value pie

        $this->assign('chartData',json_encode($chartData));

        $this->display("Tpl/Index/StockProfitChart.html");


    }


    public function showRzrq()
    {
        $chartData  = array();
   

        $RZRQModel = D('RZRQ');

        $chartData = array();
        $RZRQModel->makeSummaryChart(Market_ShangHai,"上海 指数,融资,每日增量",$chartData );
        $RZRQModel->makeSummaryChart(Market_ShenZhen,"深圳 指数,融资,每日增量",$chartData );


        $this->assign('chartData',json_encode($chartData));

        $this->display("Tpl/Index/StockRzrq.html");
    }




	public function ajaxGetMarketValue(){

        $positionModel = D("Positions");
        $marketValueModel = D("market_value");
        $positionModel->updateMarketValue();
		
        $stockCost = $positionModel->getAllPositionCost();

        $marketValueList = $marketValueModel->order('date asc')->select();
        foreach ($marketValueList as &$marketValue) {
            $marketValue['profit_percent'] = round((($marketValue['stock_value']-$marketValue['cost_value'])/$stockCost['initCash']*100),2);
            $marketValue['stock_percent'] = round($marketValue['stock_value']/($marketValue['stock_value']+$marketValue['cash_value'])*100,2);
            $marketValue['total_value'] = round(($marketValue['stock_value']+$marketValue['cash_value'])/1000,0);
            //unset($marketValue['stock_value']);
            unset($marketValue['id']);
        }
        $this->ajaxReturn($marketValueList,"成功",1);
	}




    public function showOperation($marketType = 'NASDAQ'){
        $market = D('StockInfo')->group('market')->field('market')->select();
        $this->market = $market;


        $stockModel = D('StockInfo');
        $stocks = $stockModel->where('market="'.$marketType.'"')->select();
        $this->stocks = $stocks;


        $reasons = D('op_reasons')->order('id')->select();
        $this->reasons = $reasons;

        $this->display("Tpl/Index/Operation.html");
    }

    public function showMoneyTransfer(){
        $stocks = D('StockInfo')->where('id<=3')->select();
        $this->stocks = $stocks;

        $this->display("Tpl/Index/MoneyTransfer.html");   
    }

    public function submitOperation(){

        //stock_info
        $positionsModel = D('Positions');
        $positions = $positionsModel->relation('asset_info')->where('stock_id='.$_POST['stock_id'])->select();
        if ( empty($positions[0]) ){  // not exist , add new one
            $save = array(
                'stock_id'=>$_POST['stock_id'],
                'amount'=>0,
                'total_cost'=>0,
            );
            $positionsModel->add($save);
            $positions = $positionsModel->relation('asset_info')->where('stock_id='.$_POST['stock_id'])->select();
        }

        //add to ophistory
        $save = array(
            'stock_id'=>$_POST['stock_id'],
            'amount'=>$_POST['amount'],
            'before_amount'=>$positions[0]['amount'],
            'price'=>$_POST['price'],
            'op_time'=>now(),
            'reasons'=>$_POST['reason'],
        );
        D('op_history')->add($save);


        //change amount,total_cost
        $newAmount = $_POST['amount']+$positions[0]['amount'];
        $total_cost = ($positions[0]['total_cost']+$_POST['amount']*$_POST['price']);

        //sell to 0
        $save = array(
            'id'=>$positions[0]['id'],
            'amount'=>$newAmount,
            'total_cost'=>$total_cost,
        );
        $positionsModel->save($save);


        if ($_POST['reason']!=0) //0 mean initial, not need change cash
        {
            //change cash
            $positions = $positionsModel->relation('asset_info')->where('stock_id='.$positions[0]['asset_info']['currency'])->select();
            $save = array(
                'id'=>$positions[0]['id'],
                'total_cost'=>$positions[0]['total_cost']-($_POST['amount']*$_POST['price'])
            );
            $positionsModel->save($save);
        }


        $this->ajaxReturn($_POST,"Success",1);
    }


    public function submitMoneyTransfer(){

        $positionsModel = D('Positions');
        $positions = $positionsModel->relation('asset_info')->where('stock_id='.$_POST['stock_id'])->select();
        $save = array(
            'stock_id'=>$_POST['stock_id'],
            'amount'=>$_POST['amount'],
            'before_amount'=>$positions[0]['amount'],
            'op_time'=>now(),
            'memo'=>$_POST['reason'],
        );
        D('op_history')->add($save);


        $positions = $positionsModel->relation('asset_info')->where('stock_id='.$_POST['stock_id'])->select();

        $multi =1;
        if ($_POST['type']=='out'){
            $multi = -1;
        }
        $save = array(
            'id'=>$positions[0]['id'],
            'total_cost'=>$positions[0]['total_cost']+($_POST['amount']*$multi)
        );
        $positionsModel->save($save);


        $this->ajaxReturn($_POST,"Success",1);
    }

}


