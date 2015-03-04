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
          if ($value[$valueField[0]] > $filterValue)
            array_push($newData,$value);
        }
        if ($filterType=="lessthan"){
          if ($value[$valueField[0]] < $filterValue){
            $value[$valueField[0]] = abs($value[$valueField[0]]);
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

}
