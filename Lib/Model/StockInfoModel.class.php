<?php
class StockInfoModel extends RelationModel{
    protected $tableName = 'asset_info';

    public function getIDFromSymbol($symbol){
        $stockInfo = $this->where('symbol="'.$symbol.'"')->select();
        if ( !empty($stockInfo) )    return $stockInfo[0]['id'];
        return '';
    }

    public function getSymbolFromID($id){
        $stockInfo = $this->where('id='.$id)->select();
        if ( !empty($stockInfo[0]) )    return $stockInfo[0]['symbol'];
        return '';
    }
}
