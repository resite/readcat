# -*- coding: utf-8 -*-

# Scrapy settings for crawlcat project
#
# For simplicity, this file contains only the most important settings by
# default. All the other settings are documented here:
#
#     http://doc.scrapy.org/en/latest/topics/settings.html
#

BOT_NAME = 'crawlcat'

SPIDER_MODULES = ['crawlcat.spiders']
NEWSPIDER_MODULE = 'crawlcat.spiders'
USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36'
DEPTH_LIMIT = 1
ITEM_PIPELINES = {'scrapy.contrib.pipeline.images.ImagesPipeline':1,'crawlcat.pipelines.CrawlcatPipeline':1}

IMAGES_STORE = 'images'
IMAGES_THUMBS = {'small':(140,140)}
IMAGES_EXPIRES = 90

# Crawl responsibly by identifying yourself (and your website) on the user-agent
#USER_AGENT = 'crawlcat (+http://www.yourdomain.com)'
