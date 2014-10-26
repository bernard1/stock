<?php
class IndexModel extends RelationModel{

    protected $tableName = 'stock_index_info';
	/*public $_link =  array(
       
    'stock_quarter_value'=> array(
        'mapping_type'=>HAS_MANY,
        'class_name'=>'stock_quarter_value',
        'foreign_key' =>'index_id',
        'mapping_key' =>'index_id',
        'mapping_order'=>'year,quarter',
    );*/

    public function getIndexs($stockID){
        $condi = '(stock_id='.$stockID." AND type=".IndexType_Private. " ) OR type=".IndexType_Public;
        $allIndex  = $this->where($condi)->order('`order`')->select();
        return $allIndex;
    }
    public function getNotFilledIndexs($stockID,$year,$quarter){
        $allIndex = $this->getIndexs($stockID);

        $filledList = D('index_quarter_value')->where('stock_id='.$stockID.' and year='.$year.' and quarter='.$quarter)->select();
        $retArray =array();
        $cn = 0;
        foreach ($allIndex as  $index) {
            $bFilled = false;
            foreach ($filledList as  $filled) {
                if ($index['id'] == $filled['index_id']){
                    $bFilled = true;
                    break;
                }
            }
            if (!$bFilled)
                $retArray[$cn++] = $index;
        }
        return $retArray;

    }

    public function getFilledIndexs($stockID,$year,$quarter){
        $filledList =D('index_quarter_value')->where('stock_id='.$stockID.' and year='.$year.' and quarter='.$quarter)->select();
        foreach ($filledList as &$filled) {
            $indexInfo = $this->where('id='.$filled['index_id'])->select();
            $filled['title'] = $indexInfo[0]['title'];
        }
        return $filledList;
    }
    public function getTitleByID($indexId){
        $indexInfo = $this->where('id='.$indexId)->select();
        return $indexInfo[0]['title'];
    }

}
