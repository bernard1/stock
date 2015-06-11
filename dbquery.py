import re
import logging

import pymysql
import time
import datetime

from itc.conf import *
from datetime import date, timedelta, datetime

class dbquery(object):

    def __init__(self, database):
        self.conn = pymysql.connect(host=DATABASE_HOST, port=3306, user=DATABASE_USER, passwd=DATABASE_PWD, db=database)
        logging.info('dbquery: ' + self.__str__())

    def getToFreeBookList(self,accountID):
        twoDaysAgo = (datetime.now()-timedelta(seconds=3600*24*2)).strftime('%Y-%m-%d')
        dateValue=2
        noRevenueDaysAgo = (datetime.now()-timedelta(seconds=3600*24*dateValue)).strftime('%Y-%m-%d')
        fifteenDaysAgo = (datetime.now()-timedelta(seconds=3600*24*15)).strftime('%Y-%m-%d')
        sql = 'SELECT apple_id FROM books WHERE apple_id NOT IN (\
                    SELECT DISTINCT apple_id FROM `revenue_history` WHERE date>\''+noRevenueDaysAgo+'\') \
                AND account_id = '+str(accountID) +'\
                AND friday_show=0 \
                AND last_operate_type =0  \
                AND offline=0 \
                AND last_operate_time<=\''+twoDaysAgo+'\''
        logging.info('toFreeSql:'+sql)
        return self.multiQuery(sql)

    def getLastRecordDate():
        sql = 'SELECT date FROM rzrqSummary order by date desc limit 0,1'
        return self.singleQuery(sql)


    def singleQuery(self,sql):
        cur = self.conn.cursor()
        cur.execute(sql)
        result = cur.fetchone()
        cur.close()
        return result

    def multiQuery(self,sql):
        cur = self.conn.cursor()
        cur.execute(sql)
        result = cur.fetchall()
        cur.close()
        return result

    def updateLastVersion(self,appID,lastReadyVersion,newVersion,newVersionStr,preReleaseUploadDate,preReleaseVersion):
        book = self.singleQuery('SELECT * FROM books WHERE apple_id='+appID)
        if book == None:
            print '!!!can\'t find books id:'+appID
            return -1
        if preReleaseVersion == None:
            sql = 'UPDATE books set lastReadyVersion = \''+lastReadyVersion+'\', newVersion = \''+newVersion+'\',preReleaseDate=\'\',preReleaseVersion=\'\' WHERE apple_id='+appID
        else:
            print lastReadyVersion
            print newVersion
            print newVersionStr
            print preReleaseUploadDate
            print preReleaseVersion
            sql = 'UPDATE books set lastReadyVersion = \''+lastReadyVersion+'\', newVersion = \''+newVersion+'\',preReleaseDate=\''+preReleaseUploadDate+'\',preReleaseVersion=\''+preReleaseVersion+'\' WHERE apple_id='+appID
        print sql
        self.singleQuery(sql)
        return 0
    

