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
        $indexQuarterModel = D('index_quarter_value');
        $indexChartModel = D('IndexChart');
        $indexModel = D('Index');
        //$allIndex  = D('index_quarter_value')->where($condi)->select();
        
        

        //all indexcharts
        $indexCharts= $indexChartModel->getStockIndeChart($stockid);
        $chartData = array();
        $cn = 0;

        foreach ($indexCharts as $chart) {

            // all quarter
            if ($chart['chart_type']==ChartType_serial){
                if  (strpos($chart['chart_param'],'|') )
                {

                    $graphsInfo = $this->parseChartParaDetail($chart['chart_type'],$chart['chart_param']);
                    //all field will query from database
                    //all data before calculate
                    
                    $indexValues = $indexQuarterModel->where('stock_id='.$stockid.' and index_id in('.implode(',', $graphsInfo[0]['calcFieldList']).')')->order('year,quarter,index_id')->select();

                    //compact data to 
                    //quarter  v1 v2 v3 v4
                    //xxxx-01  22 33 44 55
                    $quarterData = $this->convertIndexDataToQuarterData($indexValues);
                    //formula calculate & replace
                    $data=array();
                    foreach ($quarterData as $qd) {  //each quarter
                        $cd =array();
                        foreach ($graphsInfo as $g){ // each graph
                            $f = $g['formulaStr'];        
                            foreach ($qd as $key => $value) { //each field
                                if (strtolower(substr($key,0,7))!='quarter')
                                    $f =str_replace('id'.$key, $value, $f);
                            }
                            $cd[$g['valueField']] = round(eval($f),2);
                        }
                        $cd['quarter'] = $qd['quarter'];
                        array_push($data, $cd);
                    }

                    $indexChartModel->newChart(
                        $chart['html_name'],
                        $chart['chart_type'],
                        $data,'quarter',
                        array($graphsInfo[0]['valueField']),
                        $chartData
                    );
                }
                else
                {
                    $indexValues = $indexQuarterModel->where('stock_id='.$stockid.' and index_id='.$chart['chart_param'])->order('year,quarter,index_id')->select();
                    if (empty($indexValues[0])) continue;
            
                    $cnValue = 0;
                    $data = array();
                    foreach($indexValues as $value){ 
                       $data[$cnValue]['quarter'] = $value['year'].'0'.$value['quarter'];
                       $data[$cnValue]['value'] = $value['value'];
                       $cnValue++;
                    }
                
                    $indexChartModel->newChart(
                            $chart['html_name'],
                            $chart['chart_type'],
                            $data,
                            'quarter',
                            array('value'),
                            $chartData
                    );
                }
            }

            //all quarter pie
            if ($chart['chart_type']==ChartType_pie){
                $indexValues = $indexQuarterModel->where('stock_id='.$stockid.' and index_id in('.$chart['chart_param'].')')->order('year,quarter,index_id')->select();
                $cnValue = 0;
                $data = array();
                $quarter ='';

                $quarterData = $this->convertIndexDataToQuarterData($indexValues);
                foreach ($quarterData as $qd) {
                    $data=array();
                    $cnValue=0;
                    foreach ($qd as $key => $value) { 
                        if (strtolower(substr($key,0,7))!='quarter'){ 
                            $data[$cnValue]['title'] = $indexModel->getTitleById($key);
                            $data[$cnValue]['value'] = $value;
                        }
                        else
                            $quarter = $value;

                        $cnValue++;
                    }
                    $indexChartModel->newChart(
                        $chart['html_name'].$quarter,
                        $chart['chart_type'],
                        $data,
                        'title',
                        array('value'),
                        $chartData
                    );
                }
            } //end quarter pie.



            //
            if ($chart['chart_type']==ChartType_mutilMixedLineColumn  ){
                $graphsInfo = $this->parseChartParaDetail($chart['chart_type'],$chart['chart_param']);
                //all field will query from database
                $sqlFields = '';
                foreach ($graphsInfo as $g) {
                    if (empty($sqlFields))
                        $sqlFields = implode(',', $g['calcFieldList']);
                    $sqlFields = $sqlFields.','.implode(',', $g['calcFieldList']); 
                   // unset($g['calcFieldList']);
                }

                //all data before calculate
                $indexValues = $indexQuarterModel->where('stock_id='.$stockid.' and index_id in('.$sqlFields.')')->order('year,quarter,index_id')->select();

                //compact data to 
                //quarter  v1 v2 v3 v4
                //xxxx-01  22 33 44 55
                $quarterData = $this->convertIndexDataToQuarterData($indexValues);

                //formula calculate & replace
                $data=array();
                foreach ($quarterData as $qd) {  //each quarter
                    $cd =array();
                    foreach ($graphsInfo as $g){ // each graph
                        $f = $g['formulaStr'];        
                        foreach ($qd as $key => $value) { //each field
                            if (strtolower(substr($key,0,7))!='quarter')
                                $f =str_replace('id'.$key, $value, $f);
                        }
                        $cd[$g['valueField']] = round(eval($f),2);
                    }
                    $cd['quarter'] = $qd['quarter'];
                    array_push($data, $cd);
                }
                $indexChartModel->newChart(
                    $chart['html_name'].$quarter,$chart['chart_type'],
                    $data,'quarter',$graphsInfo,$chartData
                );
            }

        }


        $chartStocks = $indexChartModel->getHaveChartStocks();
        $this->defaultStock = $stockid;
        $this->stocks=$chartStocks;
        $this->assign('chartData',json_encode($chartData));
        $this->display("Tpl/StockIndex/ShowIndex.html");
    }


    // formula|valueFieldName|title|type|position ; formula|valueFieldName|title|type|position
    // ; 分割每个graph
    // | 分割一个graph不同参数
    // # 分割需要运算的项和运算符
    // (#id10#/id19#-#id8#)#2     (id10/id19-id18)/2
    
    private function parseChartParaDetail($type,$para)
    {
        $rets = array();
        if ($type==ChartType_mutilMixedLineColumn || $type==ChartType_serial ){
            $graphsInfo = explode(';', $para);
            foreach($graphsInfo as $g){
                $gpara = explode('|', $g);
                $formulatStr = $gpara[0];
                $calcFieldList = array();
                
                //for calculate
                $this->parseArithmetic($formulatStr,$calcFieldList);
                $ret['formulaStr'] = $formulatStr;
                $ret['calcFieldList'] = $calcFieldList;

                //for js draw graph
                $ret['valueField'] = $gpara[1];
                $ret['title'] = $gpara[2];
                $ret['type'] = $gpara[3];
                $ret['position'] = $gpara[4];
                array_push($rets, $ret);
            }
        }
        return $rets;
    }


    //parse calculate formla
    //if parameter = (#id1#-#id2#)#/#id3
    //return 
    //    formulaStr = '(id1-id2)/id3'
    //    calcFieldList = array(1,2,3);

    private function parseArithmetic(&$formulaStr, &$calcFieldList)
    {
        $formulaArray = explode('#', $formulaStr);
        $calcFieldList = array();
        $formulaStr = 'return ';
        foreach($formulaArray as $f){
            if (strtolower(substr($f,0,2))=='id'){
                $temp = substr($f,2);
                array_push($calcFieldList, $temp);
            }
            $formulaStr=$formulaStr.' '.$f;
        }
        $formulaStr=$formulaStr.';';
    }

    // in  indexData like
    // array(
    //   0=> array('year'=>2014,'quarter'=>1,'Field1'=>2);
    //   1=> array('year'=>2014,'quarter'=>2,'Field2'=>2);
    //   ....
    // )
    // return data
    // array(
    //   0=>array('quarter'=>201401,'Field1'=>2,'Field2'=>3),
    //   1=>array('quarter'=>201402,'Field1'=>2,'Field2'=>3),
    //   2=>array('quarter'=>201403,'Field1'=>2,'Field2'=>3),
    // )
    //quarter  v1 v2 v3 v4
    //xxxx-01  22 33 44 55
    private function convertIndexDataToQuarterData($indexData)
    {
        $quarterData = array();
        $cnValue = 0;
        foreach($indexData as $value){
            if (empty($quarter)){
                $quarter = $value['year'].'0'.$value['quarter'];
            }
            if ($quarter!=$value['year'].'0'.$value['quarter'] ){
                $quarter = $value['year'].'0'.$value['quarter'];
                    $cnValue++;
            }
            $quarterData[$cnValue]['quarter'] = $quarter;
            $quarterData[$cnValue][$value['index_id']] = $value['value'];    
        }
        return $quarterData;
    }

}


