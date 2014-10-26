<?php

/*

YQL
https://developer.yahoo.com/yql/guide/yql-code-examples.html
a) http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.historical
http://query.yahooapis.com/v1/public/yql?q=select%20%2a%20from%20yahoo.finance.historicaldata%20where%20symbol%20in%20%28%27YHOO%27%29%20and%20startDate%20=%20%272009-09-11%27%20and%20endDate%20=%20%272010-03-10%27&diagnostics=true&env=store://datatables.org/alltableswithkeys
b) http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.quotes
http://query.yahooapis.com/v1/public/yql?q=select%20%2a%20from%20yahoo.finance.quotes%20where%20symbol%20in%20%28%22AAPL%22%29&env=store://datatables.org/alltableswithkeys

Download Free Historical Quotes from Yahoo Finance
http://etraderzone.com/free-scripts/47-historical-quotes-yahoo.html?catid=34%3Aexcel-script

//document
https://code.google.com/p/yahoo-finance-managed/wiki/CSVAPI


http://blog.chapagain.com.np/php-how-to-get-stock-quote-data-from-yahoo-finance-complete-code-and-tutorial/
http://finance.yahoo.com/d/quotes.csv?s=GOOG+AAPL+MSFT+YHOO&f=snl1d1t1cv

FIND COMPLETE LIST OF PARAMETERS
Complete list of parameters that can be fetched from Yahoo.
a – Ask
a2 – Average Daily Volume
a5 – Ask Size
b – Bid
b2 – Ask (Real-time)
b3 – Bid (Real-time)
b4 – Book Value
b6 – Bid Size
c – Change and Percent Change
c1 – Change
c3 – Commission
c6 – Change (Real-time)
c8 – After Hours Change (Real-time)
d – Dividend/Share
d1 – Last Trade Date
d2 – Trade Date
e – Earnings/Share
e1 – Error Indication (returned for symbol changed / invalid)
e7 – EPS Est. Current Year
e8 – EPS Est. Next Year
e9 – EPS Est. Next Quarter
f6 – Float Shares
g – Day’s Low
g1 – Holdings Gain Percent
g3 – Annualized Gain
g4 – Holdings Gain
g5 – Holdings Gain Percent (Real-time)
g6 – Holdings Gain (Real-time)
h – Day’s High
i – More Info
i5 – Order Book (Real-time)
j – 52-week Low
j1 – Market Capitalization
j3 – Market Cap (Real-time)
j4 – EBITDA
j5 – Change from 52 Week Low
j6 – Percent Change from 52 Week Low
k – 52-week High
k1 – Last Trade (Real-time) with Time
k2 – Change Percent (Real-time)
k3 – Last Trade Size
k4 – Change from 52 Week High
k5 – Percent Change from 52 Week High
l – Last Trade (with time)
l1 – Last Trade (without time)
l2 – High Limit
l3 – Low Limit
m – Day’s Range
m2 – Day’s Range (Real-time)
m3 – 50 Day Moving Average
m4 – 200 Day Moving Average
m5 – Change from 200 Day Moving Average
m6 – Percent Change from 200 Day Moving Average
m7 – Change from 50 Day Moving Average
m8 – Percent Change from 50 Day Moving Average
n – Name
n4 – Notes
o – Open
p – Previous Close
p1 – Price Paid
p2 – Change in Percent
p5 – Price/Sales
p6 – Price/Book
q – Ex-Dividend Date
r – P/E Ratio
r1 – Dividend Pay Date
r2 – P/E (Real-time)
r5 – PEG Ratio
r6 – Price/EPS Est. Current Year
r7 – Price/EPS Est. Next Year
s – Symbol
s1 – Shares Owned
s7 – Short Ratio
t1 – Last Trade Time
t6 – Trade Links
t7 – Ticker Trend
t8 – 1 Year Target Price
v – Volume
v1 – Holdings Value
v7 – Holdings Value (Real-time)
w – 52 Week Range
w1 – Day’s Value Change
w4 – Day’s Value Change (Real-time)
x – Stock Exchange
y – Dividend Yield

*/


/**
 * Class to fetch stock data from Yahoo! Finance
 *
 */
include_once "Include/functions.php";

class YahooStock {
     
    /**
     * Array of stock code
     */
    private $stocks = array();
     
    /**
     * Parameters string to be fetched  
     */
    private $format;
 
    /**
     * Populate stock array with stock code
     *
     * @param string $stock Stock code of company   
     * @return void
     */
    public function addStock($stock)
    {
        $this->stocks[] = $stock;
    }
     
    
    /**
     * Populate parameters/format to be fetched
     *
     * @param string $param Parameters/Format to be fetched
     * @return void
     */
    public function addFormat($format)
    {
        $this->format = $format;
    }
 
    
    /**
     * Get Stock Data
     *
     * @return array
     */
    public function getQuotes()
    {       
        $result = array();     
        $format = $this->format;
         
        foreach ($this->stocks as $stock)
        {           
            /**
             * fetch data from Yahoo!
             * s = stock code
             * f = format
             * e = filetype
             */
            $url = "http://finance.yahoo.com/d/quotes.csv?s=$stock&f=$format&e=.csv";
            $s = file_get_contents($url);
             
            /**
             * convert the comma separated data into array
             */
            $data = explode( ',', $s);
             
            /**
             * populate result array with stock code as key
             */
            $result[$stock] = $data;
        }
        return $result;
    }


    /*
        Date Format:YYY-mm-dd
        priceType: d =day,w=week ,m=month
     */
    public function getQuoteHistory($stockSymbol,$startDate='',$endDate='',$priceType='d')
    {
        if (empty($stockSymbol)){
            echo "stockSymbol is null in yahooStock!";
            return;
        }
        if (empty($startDate))   
            $startDate = DateAdd('d',-3,now());
        if (empty($endDate))   
            $endDate = DateAdd('d',-1,now());;

        $start = explode('-',get_date($startDate));
        $end = explode('-',get_date($endDate));


        $url = "http://ichart.finance.yahoo.com/table.csv?s=$stockSymbol&a=".($start[1]-1)."&b=".$start[2].'&c='.$start[0]."&d=".($end[1]-1)."&e=".$end[2]."&f=".$end[0]."&g=$priceType";
        $s = file_get_contents($url);
        if (empty($s))    return '';

        /**
         * convert the comma separated data into array
         * populate result array with stock code as key
         */
        
        $lines = preg_split('/\n|\r\n?/', $s);

        //is title
        $first = false;
        foreach ($lines as $line) {
            if (!$first){
                $first = true;
                continue;
            }
            
            if (empty($line)) continue;

            $data = explode( ',', $line);
            $date = $data[0];
            $result[$date] = $data;
        }
         
        return $result;

    }
}
?>
