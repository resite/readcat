#!/usr/bin/env python
# -*- coding:utf-8 -*-
from scrapy.contrib.spiders import CrawlSpider, Rule
from scrapy.selector import Selector
from scrapy.contrib.linkextractors.sgml import SgmlLinkExtractor
from scrapy.http import Request, FormRequest
from crawlcat.items import CrawlcatItem

import sqlite3
import jieba
import readability
import urlparse
import re
from breadability.readable import Article

class crawlcatSpider(CrawlSpider) :
    url_sel_rules = {}
    keywords_dict = {}
    #keywords => node_id
    keywords_set = []
    #用户关键词和总结关键词
    select_keywords_dict = {}
    #cate_id => node_id
    select_keywords_set = []
    #总结关键词
    stored_url_list = []
    #本地已存网址库
    stored_title_list = []
    #已存储的标题库

    name = 'crawlcat'
    allowed_domains = []
    start_urls = []
    
    def __init__(self, category=None, *args, **kwargs):
        super(crawlcatSpider, self).__init__(*args, **kwargs)
        db_conn = sqlite3.connect('crawlcat.sqlite')
        db = db_conn.cursor()
        
        #初始化关键词
        num_list = ['0','1','2','3','4','5','6','7','8','9','一','二','三','四','五','六','七','八','九','十']
        quantifier_list = ['个','款','种','大','条','件','佳']
        num_keywords = ['排行'.decode('utf-8'),'神作'.decode('utf-8')]
        
        for num in num_list:
            for quantifier in quantifier_list:
                num_keywords.append((num+quantifier).decode('utf-8'))

        keywords = num_keywords[:]
        
        db.execute('SELECT node_id,keywords,alias_id,type_id,cate_id FROM nodes WHERE type_id < 2')
        for item in db.fetchall():
            if item[3] == 0:
                #搜索词
                self.keywords_dict[item[1]] = item[0] if item[2] == 0 else item[2]
                keywords.append(item[1])
            else:
                #精选词
                self.select_keywords_dict[item[4]] = item[0]

        self.keywords_set = set(self.keywords_dict)
        self.select_keywords_set = set(num_keywords)
        
        #生成结巴字典
        fp = open('userdict.txt','w')
        for word in keywords:
            fp.write("%s 3\n" % word.encode('utf-8'))
        fp.close()
        jieba.set_dictionary("userdict.txt")
        
        #初始化域名
        db.execute('SELECT DISTINCT domain FROM website')
        for item in db.fetchall():
            self.allowed_domains.append(item[0])
        
        #初始化地址和规则
        db.execute("SELECT url,rules,cate_id,img_rule,src_attr FROM website WHERE enabled = '1'")
        for item in db.fetchall():
            self.start_urls.append(item[0])
            self.url_sel_rules[item[0]] = {'urles':eval(item[1]),'cate_id':item[2],'img_rule':item[3],'src_attr':item[4]}
        
        #初始化已存网址
        db.execute("SELECT url,title FROM feeds");
        for item in db.fetchall():
            self.stored_url_list.append(item[0])
            #取前8个字符
            self.stored_title_list.append(item[1][:8])
        
        db.close()
        db_conn.close()
    
    
    def parse(self, response):
        rules = self.url_sel_rules[response.url]['urles']
        cate_id = self.url_sel_rules[response.url]['cate_id']
        sel = Selector(response)
        link = {}
        
        self.rselect(sel, rules, response.url, 0, link)
        
        self.mate_title_keywords(link,cate_id)
        
        for url in link:
            full_url = urlparse.urljoin(response.url,url)
            if full_url not in self.stored_url_list:
                yield Request(full_url, callback = self.parseItem,meta={'article':link[url],'ref_url':response.url})
    
    
    #循环搜索最后一个元素,a
    #返回一个 url:title 的字典
    def rselect(self, _sel, rules, _url, key, link):
        #浏览器copy来的需要过滤tbody
        rule = rules[key].replace('> tbody:nth-child(1) > ','')
        rule = rule.replace('> tbody > ','')
        sel = _sel.css(rule)
        if not sel:
            return
        if len(rules) > key+1:
            for sub_sel in sel:
                self.rselect(sub_sel, rules, _url, key+1, link)
        else:
            #最后一个元素
            title = sel.css(' ::attr(title)').extract()
            if title and title[0]:
                title = title[0]
            else:
                title = sel.css(' ::text').extract()
                title = title[0]
            url = sel.css(' ::attr(href)').extract()
            url = url[0]
            link[url] = {'title':title.strip(),'url':url}
            
            #如果规定在这里获得缩略图
            img_rule = self.url_sel_rules[_url]['img_rule']
            if img_rule:
                src_attr = self.url_sel_rules[_url]['src_attr'] if self.url_sel_rules[_url]['src_attr'] else 'src'
                img_src = _sel.css('%s ::attr(%s)' % (img_rule, src_attr)).extract()
                link[url]['image_urls'] = [img_src[0]]


    #匹配标题和关键词
    #去重复，去不匹配
    #返回一个 url:{title,keywords} 的字典
    def mate_title_keywords(self, link, cate_id):
        for url in link.keys():
            title = link[url]['title']
            
            #去重复
            if title[:8] in self.stored_title_list:
                link.pop(url)
                continue
            else:
                self.stored_title_list.append(title[:8])
                
            seg_set = set(jieba.cut_for_search(title.lower()))
            keywords_set = self.keywords_set & seg_set
            select_keywords_set = self.select_keywords_set & seg_set
            if not keywords_set and not select_keywords_set:
                link.pop(url)
            else:
                #link_dict = {'url':url,'title':title,'node_ids':[]}
                link_dict = link[url]
                link_dict['node_ids'] = []
                
                if select_keywords_set:
                    link_dict['node_ids'].append(self.select_keywords_dict[cate_id])
                if keywords_set:
                    link_dict['keywords'] = list(keywords_set)
                    for keyword in keywords_set:
                        link_dict['node_ids'].append(self.keywords_dict[keyword])
                    
                #link[url] = link_dict


    #分析网页内容，提取文章
    def parseItem(self, response):
        article = CrawlcatItem(response.meta['article'])
        article['url'] = response.url
        article['cate_id'] = self.url_sel_rules[response.meta['ref_url']]['cate_id']
        src_attr = self.url_sel_rules[response.meta['ref_url']]['src_attr']
        url_obj = urlparse.urlparse(article['url'])
        article['domain'] = url_obj.netloc
        
        #文章正文
        encoding = response.encoding
        #body = response.body if encoding == 'utf-8' else response.body.decode(encoding,'ignore').encode('utf-8')
        doc = Article(response.body, url = response.url)
        readable = doc.readable
        if src_attr:
            readable = readable.replace(' src=',' srcljj=')
            readable = readable.replace(src_attr,'src')
            
        read = readability.Readability(readable, response.url)
        article['content'] = read.content
        
        #正文图片
        if 'image_rules' not in article:
            img = re.search('<img.*?src\s*=\s*[\'"](.*?)[\'"]',read.content.lower())
            
            if img:
                article['image_urls'] = [urlparse.urljoin(response.url,img.group(1))]
        return article
