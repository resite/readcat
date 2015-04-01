# -*- coding: utf-8 -*-

# Define here the models for your scraped items
#
# See documentation in:
# http://doc.scrapy.org/en/latest/topics/items.html

import scrapy


class CrawlcatItem(scrapy.Item):
    # define the fields for your item here like:
    url = scrapy.Field()
    keywords = scrapy.Field()
    node_ids = scrapy.Field()
    content = scrapy.Field()
    title = scrapy.Field()
    domain = scrapy.Field()
    cate_id = scrapy.Field()
    
    image_urls = scrapy.Field()
    images = scrapy.Field()
