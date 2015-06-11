<?php
class IndexChartModel extends RelationModel{

    protected $tableName = 'index_chart';
	/*public $_link =  array(
       
    'stock_quarter_value'=> array(
        'mapping_type'=>HAS_MANY,
        'class_name'=>'stock_quarter_value',
        'foreign_key' =>'index_id',
        'mapping_key' =>'index_id',
        'mapping_order'=>'year,quarter',
    );*/

   public function getStockIndeChart($stockID)
   {
        $condi = 'stock_id='. IndexChart_Public . ' or stock_id='.$stockID;
        $allIndexChart = $this->where($condi)->order('`order`,`private_order`')->select();
        return $allIndexChart;  
   }


  public function getHaveChartStocks()
  {
      $havChartStock = $this->group('stock_id')->field('stock_id')->select();

      $condi = gene_sql_condition_in('id',$havChartStock,'stock_id');
      $haveChartStocks = D('StockInfo')->where($condi)->select();

      return $haveChartStocks;
  }

  public function newChart($html_name,$chartType,$data,$categoryField,$valueFields,&$array,$filterType="morethan",$filterValue=0){

    if ($chartType == ChartType_pie) //ignore zero data when pie
    {
      $newData = array();
      foreach ($data as $value) {
        if ($filterType=="morethan"){
          if ($value[$valueFields[0]] > $filterValue)
            array_push($newData,$value);
        }
        if ($filterType=="lessthan"){
          if ($value[$valueFields[0]] < $filterValue){
            $value[$valueFields[0]] = abs($value[$valueFields[0]]);
            array_push($newData,$value);
          }
        }
      }
    }
    else
    {
      $newData = $data;
    }
    $chart = array(
      'html_name'=>$html_name,
      'chart_type'=>$chartType,
      'data'=>$newData,
      'categoryField'=>$categoryField,
      'valueFields'=>$valueFields,
    );
    array_push($array,$chart);
  }

  public function getChartData($stockid)
  {
      $indexQuarterModel = D('index_quarter_value');
      $indexModel = D('Index');
      //$allIndex  = D('index_quarter_value')->where($condi)->select();
      //all indexcharts
      $indexCharts= $this->getStockIndeChart($stockid);
      $chartData = array();
      $cn = 0;
      foreach ($indexCharts as $chart) 
      {

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

                  $this->newChart(
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
              
                  $this->newChart(
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
                  $this->newChart(
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
              $this->newChart(
                  $chart['html_name'].$quarter,$chart['chart_type'],
                  $data,'quarter',$graphsInfo,$chartData
              );
          }

      }
      return $chartData;

  }//end getChartData


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
