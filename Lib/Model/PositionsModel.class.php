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

        //position
        $position = $this->relation('asset_info')->where('stock_id='.$stockInfo['id'])->select();

        $stockCost += ($rate*$position[0]['total_cost']);
        $lastClosePrice = D('StockQuoteHistory')->getLastStockQuoteWithID($stockInfo['id']);
        $stockValue = ($lastClosePrice*$rate*$position[0]['amount']);

        return array(
            'stockCost'=>round($stockCost ,2),
            'stockValue'=>round($stockValue,2),
            'profit'=>round($stockValue-$stockCost,2),
            'profitPercent'=>round((($stockValue-$stockCost)/$allCost['initCash']*100),2)
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
        while( DateDiff('d',$beginDate,now()) >=1 ){
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
                    if ($closePrice==0){
                        $havePrice0 = true;
                        break;
                    }
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
