<?php

/*********************************************************#

#*********************************************************/
class StockIndexAction extends Action {

	public function _initialize(){
        include_once "Include/define.php";
        include_once "Include/functions.php";
        include_once "Include/class.yahoostock.php";

         $this->assign('baseUrl',C('TEMPALTE_BASE_URL'));


	}


	
    public function showManage($defaultType = 0,$defaultStock=0)
    {
        $allStock = D('StockInfo')->where('type='.AssetType_Stock)->select();

        if ($defaultType==IndexType_Public){
            $condi = 'type='.IndexType_Public;
        }
        else{
            $condi = 'stock_id='.$defaultStock." AND type=".IndexType_Private;   
        }

        $allIndexs = D('stock_index_info')->where($condi)->select();
        
        $this->indexs = $allIndexs;


        $noneStock['id']=0;
        $noneStock['symbol']='None';
        array_unshift($allStock,$noneStock);

        $this->stocks = $allStock;
    	$this->defaultType = $defaultType;
    	$this->defaultStock = $defaultStock;
        $this->display("Tpl/StockIndex/Manage.html");
        
    }


    public function submitIndex()
    {
        $saveArray = array( 
            'stock_id'=>$_POST['stock_id'],
            'title'=>$_POST['title'],
            'type'=>$_POST['type'],
         );
        D('stock_index_info')->add($saveArray);

        $url = C("TEMPALTE_BASE_URL")."StockIndex/showManage/defaultType/".$_POST['type']."/defaultStock/".$_POST['stock_id'];
        $this->ajaxReturn($url,'Success',1);
    }
    public function deleteIndex()
    {

        $condi =  'stock_id='.$_POST['stock_id'].' AND id='.$_POST['index_id'].' AND type='.$_POST['type'];
        
        D('stock_index_info')->where($condi)->delete();

        $url = C("TEMPALTE_BASE_URL")."StockIndex/showManage/defaultType/".$_POST['type']."/defaultStock/".$_POST['stock_id'];
        $this->ajaxReturn($url,'Success',1);
    }

    public function deleteIndexValue()
    {
        $condi =  'id='.$_POST['id'];
        $indexValue = D('index_quarter_value')->where($condi)->select();
        D('index_quarter_value')->where($condi)->delete();
        $url = C("TEMPALTE_BASE_URL")."StockIndex/showFillIndexValue/defaultYear/".$indexValue[0]['year']."/defaultQuarter/".$indexValue[0]['quarter']."/defaultStock/".$indexValue[0]['stock_id'];
        $this->ajaxReturn($url,'Success',1);    
    }
	
    public function ajaxGetIndexs($type,$stock)
    {

        if ($type==IndexType_Public){
            $condi = 'type='.IndexType_Public;
        }
        else{
            $condi = 'stock_id='.$stock." AND type=".IndexType_Private;   
        }
        $allIndexs = D('stock_index_info')->where($condi)->select();
        $this->ajaxReturn($allIndexs,'Success',1);
    }




    public function showFillIndexValue($defaultStock=10,$defaultYear = 2014,$defaultQuarter=1)
    {
        $allStock = D('StockInfo')->where('type='.AssetType_Stock)->select();
        $condi = 'stock_id='.$defaultStock." AND year=".$defaultYear." AND quarter=".$defaultQuarter;

        $indexModel = D('Index');
        $quarterValues = $indexModel->getFilledIndexs($defaultStock,$defaultYear,$defaultQuarter);
        $allIndex = $indexModel->getNotFilledIndexs($defaultStock,$defaultYear,$defaultQuarter);

        $noneStock['id']=0;
        $noneStock['symbol']='None';
        array_unshift($allStock,$noneStock);
        $this->values = $quarterValues;
        $this->stocks = $allStock;
        $this->indexs = $allIndex;
        $this->defaultStock = $defaultStock;
        $this->defaultYear = $defaultYear;
        $this->defaultQuarter = $defaultQuarter;
        $this->display("Tpl/StockIndex/FillValue.html");
    }

    public function ajaxGetNeedFillIndexs($stock,$year,$quarter)
    {
        $indexModel = D('Index');

        $allIndex = $indexModel->getNotFilledIndexs($stock,$year,$quarter);
        $this->ajaxReturn($allIndex,'Success',1);
    }
    public function submitIndexValue()
    {
         $saveArray = array(
            'stock_id'=>$_POST['stock'],
            'value'=>$_POST['value'],
            'year'=>$_POST['year'],
            'quarter'=>$_POST['quarter'],
            'index_id'=>$_POST['index'],
         );
        D('index_quarter_value')->add($saveArray);

        $url = C("TEMPALTE_BASE_URL")."StockIndex/showFillIndexValue/defaultYear/".$_POST['year']."/defaultQuarter/".$_POST['quarter']."/defaultStock/".$_POST['stock'];
        $this->ajaxReturn($url,'Success',1);
    }
    public function showIndex($stockid = 10)
    {
/*        $str = 'return 2'.' '.'*'.' '.'301;';
        $a =  eval($str);
        MYDUMP("===");
        MYDUMP($str.$a);
        die;*/

        $indexChartModel = D('IndexChart');
        $chartData = $indexChartModel->getChartData($stockid);
        

        $chartStocks = $indexChartModel->getHaveChartStocks();
        $this->defaultStock = $stockid;
        $this->stocks=$chartStocks;
        $this->assign('chartData',json_encode($chartData));
        $this->display("Tpl/StockIndex/ShowIndex.html");
    }


 

}


