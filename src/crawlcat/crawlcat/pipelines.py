# -*- coding: utf-8 -*-

# Define your item pipelines here
#
# Don't forget to add your pipeline to the ITEM_PIPELINES setting
# See: http://doc.scrapy.org/en/latest/topics/item-pipeline.html

from datetime import datetime, timedelta
from math import log
import time
import sqlite3
import MySQLdb
from ftplib import FTP

class CrawlcatPipeline(object):
    sqlite_conn = None
    sqlite_db = None
    mysql_conn = None
    mysql_db = None
    ftp = None
    date_str = None
    epoch = None
    
    def __init__(self):
        self.epoch = datetime(1970, 1, 1)
        now = int(time.time())
        self.sqlite_conn = sqlite3.connect('crawlcat.sqlite')
        self.sqlite_db = self.sqlite_conn.cursor()
        
        self.sqlite_db.execute("DELETE FROM feeds WHERE add_time < '%s'" % (now-7*24*3600))
        self.sqlite_conn.commit()
        
        self.mysql_conn = MySQLdb.connect(host='localhost',user='root',passwd='',db='readcat',port=3306,use_unicode=True,charset='utf8')
        self.mysql_db = self.mysql_conn.cursor()
        
        '''
        ftp = FTP()
        self.ftp = ftp
        ftp.connect('115.29.194.224','21')
        ftp.login('www','AaKeDgNrf')
        #print ftp.getwelcome()#显示ftp服务器欢迎信息 
        '''
        
        date = datetime.now()
        date = date.strftime('%Y%m%d')
        self.date_str = date
        '''
        try:
            ftp.cwd('readcat/contents/images/full/'+date) #选择操作目录
            ftp.cwd('../../../../../')
        except:
            ftp.mkd('readcat/contents/images/full/'+date)
        try:
            ftp.cwd('readcat/contents/images/thumbs/small/'+date)
            ftp.cwd('../../../../../../')
        except:
            ftp.mkd('readcat/contents/images/thumbs/small/'+date)
        '''
    
    def __del__(self):
        self.sqlite_db.close()
        self.sqlite_conn.close()
        
        
        self.mysql_db.close()
        self.mysql_conn.close()
        '''
        self.ftp.quit()
        self.ftp.close()
        '''
        
    def process_item(self, item, spider):
        db_conn = self.sqlite_conn
        db = self.sqlite_db
        
        add_time = int(time.time())
        top_image = item['images'][0]['path'][5:] if item['images'] else ''
        
        sql = "INSERT INTO feeds (`url`,`title`,`content`,`add_time`,`top_image`) VALUES ('%s','%s','%s','%s','%s');" % (item['url'], item['title'].replace("'","''"), item['content'].replace("'","''"), add_time, top_image)
        db.execute(sql)
        feed_id = db.lastrowid
        for node_id in item['node_ids']:
            sql = "INSERT INTO node_feed_relation (`feed_id`,`node_id`) VALUES ('%s','%s');" % (feed_id,node_id)
            db.execute(sql)
            
        db_conn.commit()
        
        #self.post(item, add_time, top_image)
        
        return item

    def post(self, item, add_time, top_image):
        #复制图片文件
        if top_image:
            fh = open('images/full/'+top_image,'rb')
            self.ftp.storbinary('STOR readcat/contents/images/full/%s/%s' % (self.date_str,top_image),fh,1024)#上传文件 
            fh.close()
            fh = open('images/thumbs/small/'+top_image,'rb')
            self.ftp.storbinary('STOR readcat/contents/images/thumbs/small/%s/%s' % (self.date_str,top_image),fh,1024)#上传文件 
            fh.close()
            top_image = self.date_str+'/'+top_image
        
        #发送到远端mysql数据库
        db_conn = self.mysql_conn
        db = self.mysql_db
        
        #计算得分，忽略两位小数并且转换成整形
        date = datetime.now()
        hot_score = int(self.hot(0, 0, date)*100000)
        
        sql = "INSERT INTO feeds (`cate_id`,`url`,`title`,`content`,`add_time`,`top_image`,`ups`,`downs`,`hot_score`,`domain`,`status`) VALUES ('%s','%s','%s','%s','%s','%s','0','0','%s','%s','1');" % (item['cate_id'],item['url'], item['title'].replace("'","''"), item['content'].replace("'","''"), add_time, top_image, hot_score,item['domain'])
        db.execute(sql)
        feed_id = db.lastrowid
        for node_id in item['node_ids']:
            sql = "INSERT INTO node_feed_relation (`feed_id`,`node_id`) VALUES ('%s','%s');" % (feed_id,node_id)
            db.execute(sql)
        
        db_conn.commit()
        
    def epoch_seconds(self, date):
        """Returns the number of seconds from the epoch to date."""
        td = date - self.epoch
        #return td.days * 86400 + td.seconds + (float(td.microseconds) / 1000000)
        #为了方便存储且方便php运算，忽略microseconds
        return float(td.days * 86400 + td.seconds)
 
    def score(self, ups, downs):
        return ups - downs
     
    def hot(self, ups, downs, date):
        """The hot formula. Should match the equivalent function in postgres."""
        s = self.score(ups, downs)
        order = log(max(abs(s), 1), 10)
        sign = 1 if s > 0 else -1 if s < 0 else 0
        seconds = self.epoch_seconds(date) - 1134028003
        return round(order * sign + seconds / 45000, 7)
        