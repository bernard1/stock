<?php
class AnalysisModel extends RelationModel{

    public function opAnalysis(){
        include_once "Include/functions.php";
        $deci =$this->allOpAnalysis(HALF_YEAR_ANALYSIS);

        $right= 0;
        foreach ($deci[0] as $key => $value) {
            $right = $right+$value;
        }

        foreach ($deci[1] as $key => $value) {
            $wrong = $wrong+$value;
        }
        MYDUMP($deci);
        MYDUMP($right);
        MYDUMP($wrong);
    }

    //each operation
    private function allOpAnalysis($periodType = HALF_YEAR_ANALYSIS){
        if ($periodType == HALF_YEAR_ANALYSIS)
            $addDate = 30*6;
        else
            $addDate = 30*3;
        $hModel = D('op_history');
        $hall = $hModel->order('op_time asc')->select();
        $qtModel = D('stock_quote_history');
        $rDeci = array();
        $wDeci = array();
        foreach ($hall as $h) {
            $qtDate = DateAdd('d',$addDate,$h['op_time']);
            $qtInfo = $qtModel->where('stock_id='.$h['stock_id'].' AND date<="'.$qtDate.'"')->order('date desc')->limit(1)->select();
            if (empty($qtInfo[0])){
                MYDUMP($h);
                MYDUMP($qtModel->getLastSql());
                continue;
            }

            if ($h['amount']>0){
                if ($qtInfo[0]['adj_close']> $h['price'] )  //right decision
                {
                    $rDeci[$h['stock_id']] = empty($rDeci[$h['stock_id']])? 1: ++$rDeci[$h['stock_id']];
                }
                else
                    $wDeci[$h['stock_id']] = empty($wDeci[$h['stock_id']])? 1: ++$wDeci[$h['stock_id']];   
            }
            else{
                if ($qtInfo[0]['adj_close']< $h['price'] )  //right decision
                {
                    $rDeci[$h['stock_id']] = empty($rDeci[$h['stock_id']])? 1: ++$rDeci[$h['stock_id']];
                }
                else
                    $wDeci[$h['stock_id']] = empty($wDeci[$h['stock_id']])? 1: ++$wDeci[$h['stock_id']];   

            }

        }
        $ret =array($rDeci,$wDeci);
        return $ret;
    }
}
