<?php
class PositionsModel extends RelationModel{
	  public $_link =  array(
       
        'asset_info'=> array(
            'mapping_type'=>BELONGS_TO,
            'foreign_key'=>'stock_id',
            'mapping_fields'=>'symbol,name,id,market,currency,type',
        ),
    );

    //////////////////////////////////////////////////
    // each hold stock value and precentage         //
    // index, symbol, value, percentage             //
    //////////////////////////////////////////////////
    public function getStocksValuePercentage($type='long', $order=SORT_DESC)
    {
        $positionModel = D("Positions");
        $allPosition = $positionModel->relation('asset_info')->order('id asc')->select();
        $holdInfo = array();
        $holdCount = 0;
        foreach ($allPosition as $position) {
            if ($position['asset_info']['type']!=AssetType_Stock) continue;
            
            $info = $positionModel->getEachStockCostVaue($position['asset_info']['symbol']);
            if ($type=='long' && $info['stockValue']<=0)    continue;
            if ($type=='short' && $info['stockValue']>=0)    continue;
            $holdInfo[$holdCount] = $info;
            $holdInfo[$holdCount]['symbol'] = $position['asset_info']['symbol'];
            $holdInfo[$holdCount]['name'] = $position['asset_info']['name'];
            $holdCount++;
        }
        $allValue = array_sum(array_column($holdInfo, 'stockValue'));

        foreach ($holdInfo as &$hold) {
            $hold['percentage'] = round($hold['stockValue']*100/$allValue,2);
        }
        $holdInfo = array_sort($holdInfo,'percentage',$order);
        $holdInfo[$holdCount]['symbol']='total';
        $holdInfo[$holdCount]['name']='----';
        $holdInfo[$holdCount]['stockValue']=$allValue;
        $holdInfo[$holdCount]['percentage']=100;

        return $holdInfo;
    }
    

    //////////////////////////////////////////////////
    // each stock total cost and value and profile      //
    // some time when earn money , cost maybe is <0 //
    //////////////////////////////////////////////////
    public function getEachStockCostVaue($symbol)
    {
        //currency
        $currency = D('Currency')->getRateArray();
        //stockinfo
        $stockInfo = D('StockInfo')->getBySymbol($symbol);

        $rate = $currency[ $stockInfo['currency'] ];
        
        $allCost = D('positions')->getAllPositionCost();
        $modelQuoteHstory = D("StockQuoteHistory");
        //position
        $position = $this->relation('asset_info')->where('stock_id='.$stockInfo['id'])->select();

        $stockCost += ($rate*$position[0]['total_cost']);
        $lastClosePrice = D('StockQuoteHistory')->getLastStockQuoteWithID($stockInfo['id']);
        if ($lastClosePrice ==0){
            if ($stockInfo['market']=='ShangHaiA' || $stockInfo['market']=='ShenZhenA')
            {
                $symbol = explode(".", $stockInfo['symbol']);
                if ($stockInfo['market']=='ShangHaiA')
                    $sybl = 'sh'.$symbol[0];
                else
                    $sybl = 'sz'.$symbol[0];
                $lastClosePrice = $modelQuoteHstory->getLastQuoteFromSina($sybl);
            }
        }
        $stockValue = ($lastClosePrice*$rate*$position[0]['amount']);

        return array(
            'stockCost'=>floor($stockCost),
            'stockValue'=>floor($stockValue),
            'profit'=>floor($stockValue-$stockCost)
            //'profitPercent'=>floor((($stockValue-$stockCost)/$allCost['initCash']*100),2)
        );
    }


    //
    public function updateMarketValue()
    {
        //currency
        $currency = D('Currency')->getRateArray();
        
        $SQHModal = D('StockQuoteHistory');
        
        $positionAll = $this->relation('asset_info')->select();


        //last marketvalue date
        $marketValueModal = D('market_value');
        $lastValueDate = $marketValueModal->field('max(date) as date')->select();
        if (empty($lastValueDate[0]['date'])){  //one month ago
            $beginDate = DateAdd('d',-29,now());
        }else{
            $beginDate = DateAdd('d',1,$lastValueDate[0]['date']);
        }
        $beginDate = get_date($beginDate);
        //each date stock value
        $stockValue = array();
        $modelQuoteHstory = D("StockQuoteHistory");

        while( DateDiff('d',$beginDate,now()) >=1 ){
            echo "here";
            $cashValue = 0;
            $stockCost = 0;
            $stockVal = 0;
            $havePrice0 = false;
            //each positions
            foreach ($positionAll as $postion) {
				if ($postion['amount']==0) continue;
                $rate = $currency[ $postion['asset_info']['currency'] ];

                if ($postion['asset_info']['type'] == AssetType_Stock){
                    $stockCost += ($rate*$postion['total_cost']);
                    //$quoteHistory   
                    $dateTime = explode(' ',$beginDate);
                    $closePrice = $SQHModal->getQuoteByDate($postion['asset_info']['id'],$dateTime[0]);
                    echo $postion['asset_info']['symbol'].":".$closePrice."<br>";

                    //can't find this date close quote price.
                    //use sina get today's price
                    if ($closePrice==0){
                        if ($postion['asset_info']['market']=='ShangHaiA' || $postion['asset_info']['market']=='ShenZhenA')
                        {
                            $symbol = explode(".", $postion['asset_info']['symbol']);
                            if ($postion['asset_info']['market']=='ShangHaiA')
                                $sybl = 'sh'.$symbol[0];
                            else
                                $sybl = 'sz'.$symbol[0];
                            $closePrice = $modelQuoteHstory->getLastQuoteFromSina($sybl);
                            echo "use sina.".$sybl.$closePrice."\r\n";
                        }
                        if ($closePrice==0){
                            $havePrice0 = true;
                            break;
                        }
                    }
                    if ($postion['amount']>0)
                        $stockVal += ($closePrice*$rate*$postion['amount']);
                    

                    //$stockCost += ($rate*$postion['amount']*$postion['total_cost']);
                }
            }//each position
            if ($havePrice0 == false){
                $stockValue[$beginDate] = $stockVal;
            }
            $beginDate = DateAdd('d',1,$beginDate);
        } //end each date
        $costCash = $this->getAllPositionCost();

        //save marketValue
        foreach ($stockValue as $date => $value) {
            $saveArray = array(
                'date'=>$date,
                'cash_value'=>$costCash['cashValue'],
                'stock_value'=>$value,
                'cost_value'=>$costCash['stockCost'],
            );
            $marketValueModal->Add($saveArray);
        }

        
    }

    public function getAllPositionCost()
    {
        //currency
        $currency = D('Currency')->getRateArray();

        $positionAll = $this->relation('asset_info')->select();



        $cashValue = 0;
        $stockCost = 0;
        $initCash = 0;

        //each positions cost and cash
        foreach ($positionAll as $postion) {
            $rate = $currency[ $postion['asset_info']['currency'] ];
            $value = ($rate*$postion['total_cost']);
            if ($postion['asset_info']['type'] == AssetType_Stock){
                 if ($postion['amount']>0)
                    $stockCost += $value;
            }
            else if ($postion['asset_info']['type'] == AssetType_Currency){
                $cashValue += $value;
            }
        }

        $initCaptial = D("init_capital")->select();
        foreach ($initCaptial as $value) {
            $initCash += $value['amount']*$currency[$value['currency']];
        }

        return array('stockCost'=>$stockCost,'cashValue'=>$cashValue,'initCash'=>$initCash);
    }

}
