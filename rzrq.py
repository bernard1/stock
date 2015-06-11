#!/usr/bin/env python
# encoding: utf-8
import urllib2
import xlrd 
import pymysql
import time
import datetime
import os.path
from datetime import date, timedelta, datetime

localPath = '/Users/bernard_xu/Documents/StockRzrqExcel/'
beginDate = '2014-11-03'
conn = pymysql.connect(host="192.168.1.23", port=3306, user="root", passwd="123123", db="stock", charset='utf8')
def singleQuery(sql):
    cur = conn.cursor()
    cur.execute(sql)
    result = cur.fetchone()
    cur.close()	
    return result

def processSHFile(fname,timestamp):
	if (os.path.isfile(fname) == False): 
		return
	strDate = datetime.fromtimestamp(timestamp).strftime('%Y-%m-%d')

	print "process:"+fname
	sum_today_rz_sum = 0 #本日融资余额(万元)
	sum_today_rz_buy = 1 #本日融资买入额(万元)
	sum_today_rj_volume = 2 #本日融券余量
	sum_today_rj_sum = 3  #本日融券余量金额(万元)
	sum_today_rj_sell_volume=4 # float DEFAULT NULL COMMENT '本日融券卖出量
	sum_today_rzrq_sum=5  #本日融资融券余额(万元)

	stock_stock_id = 0
	stock_stock_name = 1
	stock_today_rz_margin = 2 #本日融资余额(万元)
	stock_today_rz_buy = 3 #本日融资买入额(万元)
	stock_today_rz_return = 4 #本日融资偿还额(万元)
	stock_today_rj_margin_volume = 5  #本日融券余量
	stock_today_rj_sell_volume = 6 # 本日融券卖出量
	stock_today_rj_return_volume = 7  #本日融券偿还量


	book = xlrd.open_workbook(fname)
	for sheet_name in book.sheet_names():
		sheet = book.sheet_by_name(sheet_name)
		r = sheet.row_values(1)
		if (sheet_name == u"汇总信息"):
			for x in range(0,sum_today_rzrq_sum+1):
				r[x] = str(int(r[x])/10000)
			sql = 'INSERT INTO `rzrq_summary` ( `today_rz_sum`, `today_rz_buy`, `today_rj_volume`, `today_rj_sum`, `today_rj_sell_volume`, `today_rzrq_sum`, `market`, `date`) VALUES ('

			sql = sql+r[sum_today_rz_sum]
			sql = sql+','+r[sum_today_rz_buy]
			sql = sql+','+r[sum_today_rj_volume]
			sql = sql+','+r[sum_today_rj_sum]
			sql = sql+','+r[sum_today_rj_sell_volume]
			sql = sql+','+r[sum_today_rzrq_sum]+', 1, "'+strDate+'")'
			#print sql
			singleQuery(sql)
		print 'end process summary'
		if (sheet_name == u"明细信息"):
			for row_num in range(1,sheet.nrows):
				r = sheet.row_values(row_num)
				for x in xrange(2,8):
					r[x] = str(int(r[x])/10000)
				sql = 'INSERT INTO `rzrq_each_stock` ( `stock_id`, `stock_name`, `today_rz_margin`, `today_rz_buy`, `today_rz_return`, `today_rj_margin_volume`, `today_rj_sell_volume`, `today_rj_return_volume`, `market`, `date`) VALUES ('
				sql = sql+ '"'+r[stock_stock_id]+'"'
				sql = sql+',"'+r[stock_stock_name]+'"'
				sql = sql+','+r[stock_today_rz_margin]
				sql = sql+','+r[stock_today_rz_buy]
				sql = sql+','+r[stock_today_rz_return]
				sql = sql+','+r[stock_today_rj_margin_volume]
				sql = sql+','+r[stock_today_rj_sell_volume]
				sql = sql+','+r[stock_today_rj_return_volume]+', 1, "'+strDate+'")'
				#print sql
				singleQuery(sql)
		print 'end process each stock'
#process SZ File
from lxml import etree


def processSZFile(fsummary,feachstock,timestamp):
	print fsummary
	print feachstock
	if (os.path.isfile(fsummary) == False): 
		return
	if (os.path.isfile(feachstock) == False): 
		return
	strDate = datetime.fromtimestamp(timestamp).strftime('%Y-%m-%d')


	print "process:"+fsummary
	sum_today_rz_buy = 0 #本日融资买入额(万元)
	sum_today_rz_sum = 1 #本日融资余额(万元)
	sum_today_rj_sell_volume=2 # 本日融券卖出量
	sum_today_rj_volume = 3 #本日融券余量
	sum_today_rj_sum = 4  #本日融券余量金额(万元)
	sum_today_rzrq_sum=5  #本日融资融券余额(万元)

	stock_stock_id = 0
	stock_stock_name = 1
	stock_today_rz_buy = 2 #本日融资买入额(万元)
	stock_today_rz_margin = 3 #本日融资余额(万元)
	stock_today_rj_sell_volume = 4 # 本日融券卖出量
	stock_today_rj_margin_volume = 5  #本日融券余量
	stock_today_rj_margin = 6  #融券余额(万元)
	stock_today_rzrj_margin = 7 #融资融券余额(万元)

	parser = etree.HTMLParser()
	

	#summary xpath parse
	tree = etree.parse(fsummary, parser)
	alltd = tree.xpath("//table[@class='cls-data-table']/tr[2]/*")
	#for td in alltd:
	#	print td.text
	if len(alltd)<4:
		return
	r = [None]*(sum_today_rzrq_sum+1)
	for x in range(0,sum_today_rzrq_sum+1):
		number = str(alltd[x].text)
		number = number.replace(",","")
		r[x] = str(int(number)/10000)
	sql = 'INSERT INTO `rzrq_summary` ( `today_rz_sum`, `today_rz_buy`, `today_rj_volume`, `today_rj_sum`, `today_rj_sell_volume`, `today_rzrq_sum`, `market`, `date`) VALUES ('

	sql = sql+r[sum_today_rz_sum]
	sql = sql+','+r[sum_today_rz_buy]
	sql = sql+','+r[sum_today_rj_volume]
	sql = sql+','+r[sum_today_rj_sum]
	sql = sql+','+r[sum_today_rj_sell_volume]
	sql = sql+','+r[sum_today_rzrq_sum]+', 2, "'+strDate+'")'
	singleQuery(sql)
	#
	#end process summary


	#each stock xpath parse

	tree = etree.parse(feachstock, parser)
	alltr = tree.xpath("//table[@class='cls-data-table']/*")
	i = 0
	for alltd in alltr:
		i = i+1
		if (i==1):  #ignore first line is title 
			continue;
		r = [None]*(len(alltd))
		j = 0
		for x in range(0,len(alltd)):
			j = j+1
			if (j<=2): #id and name
				r[x] = alltd[x].text
				continue  
			number = str(alltd[x].text)
			number = number.replace(",","")
			r[x] = str(int(number)/10000)
		sql = 'INSERT INTO `rzrq_each_stock` ( `stock_id`, `stock_name`, `today_rz_margin`, `today_rz_buy`, `today_rj_margin_volume`, `today_rj_sell_volume`, `today_rj_margin`, `today_rzrj_margin`, `market`, `date`) VALUES ('
		sql = sql+ '"'+r[stock_stock_id]+'"'
		sql = sql+',"'+r[stock_stock_name]+'"'
		sql = sql+','+r[stock_today_rz_margin]
		sql = sql+','+r[stock_today_rz_buy]
		sql = sql+','+r[stock_today_rj_margin_volume]
		sql = sql+','+r[stock_today_rj_sell_volume]
		sql = sql+','+r[stock_today_rj_margin]
		sql = sql+','+r[stock_today_rzrj_margin]+', 2, "'+strDate+'")'
		print sql
		singleQuery(sql)


def downAndProcessSHFile(localPath,tstampDate):
	#shanghai
	#summary&each_stock http://www.sse.com.cn/market/dealingdata/overview/margin/a/rzrqjygk%d%02d%02d.xls
	urlname = 'http://www.sse.com.cn/market/dealingdata/overview/margin/a/'

	fname = 'rzrqjygk%d%02d%02d.xls'
	tDate = datetime.fromtimestamp(tstampDate)
	fname = fname % ( tDate.year , tDate.month, tDate.day )
	
	urlname = urlname+fname
	pathfile = localPath+'sh/'+fname

	if downloadFileFromWeb(pathfile,urlname)==1:
		processSHFile(pathfile,tstampDate)


#Download ShenZhen Stock Market
def downAndProcessSZFile(localPath,tstampDate):
	#shenzen
	#summary http://www.szse.cn/szseWeb/FrontController.szse?ACTIONID=8&CATALOGID=1837_xxpl&txtDate=%d-%02d-%02d&tab2PAGENUM=1&ENCODE=1&TABKEY=tab1
	#each_stock http://www.szse.cn/szseWeb/FrontController.szse?ACTIONID=8&CATALOGID=1837_xxpl&txtDate=%d-%02d-%02d&tab2PAGENUM=1&ENCODE=1&TABKEY=tab2

	tDate = datetime.fromtimestamp(tstampDate)


	fdate='%d-%02d-%02d'
	fdate = fdate % ( tDate.year , tDate.month, tDate.day )
	
	fsummary = 'http://www.szse.cn/szseWeb/FrontController.szse?ACTIONID=8&CATALOGID=1837_xxpl&txtDate='+fdate+'&tab2PAGENUM=1&ENCODE=1&TABKEY=tab1'
	feachstock='http://www.szse.cn/szseWeb/FrontController.szse?ACTIONID=8&CATALOGID=1837_xxpl&txtDate='+fdate+'&tab2PAGENUM=1&ENCODE=1&TABKEY=tab2'

	pathfilesummary = localPath+'sz/s'+fdate+'.html'
	pathfileeachstock = localPath+'sz/e'+fdate+'.html'

	#print downloadFileFromWeb(pathfilesummary,fsummary)
	#print downloadFileFromWeb(pathfileeachstock,feachstock)

	processSZFile(pathfilesummary,pathfileeachstock,tstampDate)



def downloadFileFromWeb(pathfile,urlname):
	try:
		print urlname
		response = urllib2.urlopen(urlname)
		html = response.read()
		file = open(pathfile, 'a+')
		file.write(html)
		file.close()
		return 1
	except Exception, e:
		print e
		return 0


strDate=singleQuery('SELECT date FROM rzrq_summary ORDER BY date DESC LIMIT 0,1')
if strDate == None :
	strDate = beginDate
else:
	strDate = strDate[0].strftime("%Y-%m-%d")

strDate = '2014-11-01'

tstampDate = time.mktime(time.strptime(strDate, "%Y-%m-%d"))
tstampNow = time.mktime(time.strptime( datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d'), "%Y-%m-%d"))


while (tstampDate<tstampNow):
	if datetime.fromtimestamp(tstampDate).weekday()<5:
		#downAndProcessSHFile(localPath,tstampDate)
		downAndProcessSZFile(localPath,tstampDate)
	tstampDate +=3600*24







