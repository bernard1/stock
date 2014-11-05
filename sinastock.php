<?php
$stockCode = 600000
$url = "http://money.finance.sina.com.cn/corp/go.php/vMS_MarketHistory/stockid/{0}.phtml" -f $stockCode
$wc = New-Object System.Net.WebClient
$content = $wc.DownloadString($url)

$reg = "<a target='_blank'\s+href='http://biz.finance.sina.com.cn/stock/history_min.php\?symbol=sh\d{6}&date=\d{4}-\d{2}-\d{2}'>\s*([^\s]+)\s+</a>\s*</div></td>\s*<td[^\d]*([^<]*)</div></td>\s+<td[^\d]*([^<]*)</div></td>\s+<td[^\d]*([^<]*)</div></td>\s+<td[^\d]*([^<]*)</div></td>\s+"
$result = [RegEx]::matches($content, $reg)

foreach($item in $result)
{
    $date = $item.Groups[1].Value # 时间
    $opening = $item.Groups[2].Value # 开盘
    $maxHigh = $item.Groups[3].Value # 最高
    $closing = $item.Groups[4].Value # 收盘
    $maxLow = $item.Groups[5].Value # 最低
    Write-Host $date $opening $maxHigh $closing $maxLow
}
?>
