#!/usr/bin/python
# coding: UTF-8
import os.path
from  datetime  import  *
import  time
import urllib
import urllib2
import json
import re
import codecs
class StockData:
    DOMAIN = 'http://vip.stock.finance.sina.com.cn/'
    page_num = 500
    urlMap = {
        1: {
            'name' : '主力净流量',
            'url' : 'quotes_service/api/json_v2.php/MoneyFlow.ssl_bkzj_ssggzj',
            'param' : {
                'sort' : 'r0_ret',
                'num' : 20,
                'asc' : 0,
                'bankuai' : '',
                'shichang' : ''
            },
            'fileTitle' : '代码,名称,最新价,涨跌幅,换手率,成交额/万,主力流出/万,主力流入/万,主力净流入/万,主力静流入率,主力罗盘',
            'dataKey' : ['symbol', 'name', 'trade', 'changeratio', 'turnover', 'amount', 'r0_out', 'r0_in', 'r0_net', 'r0_ratio', 'r0x_ratio']
        },
        2: {
            'name' : '净流入排名',
            'url' : 'quotes_service/api/json_v2.php/MoneyFlow.ssl_bkzj_ssggzj',
            'param' : {
                'sort' : 'netamount',
                'num' : 20,
                'asc' : 0,
                'bankuai' : '',
                'shichang' : ''
            },
            'fileTitle' : '代码,名称,最新价,涨跌幅,换手率,成交额/万,流出资金/万,流入资金/万,净流入/万,静流入率,主力罗盘',
            'dataKey' : ['symbol', 'name', 'trade', 'changeratio', 'turnover', 'amount', 'outamount', 'inamount', 'netamount', 'ratioamount', 'r0x_ratio']
        }
    }
    operationMap = {
        'turnover' : 100,
        'r0_net' : 10000,
        'amount' : 10000,
        'r0_out' : 10000,
        'r0_in' : 10000,
        'outamount' : 10000,
        'inamount' : 10000,
        'netamount' : 10000
    }
    def __init__(self):
        output = "请输入最大页码"
        print self.coding(output)
        num = raw_input("")
        self.page_num = int(num)

    def demo(self):
        for key, value in self.urlMap.items():
            print (str(key) + ' corresponds to ' + self.coding(value['name']))
            self.getData(value)

    def putFileTitle(self, file_obj, title):
        output = self.coding(title) + "\n"
        file_obj.write(output)

    def coding(self, strs, cfrom = 'utf-8', cto = 'gbk'):
        return strs.decode(cfrom).encode(cto)

    def invoke(self, url, param):
        tmp = urllib.urlencode(param)
        url = self.DOMAIN + url + '?' + tmp
        try:
            data = urllib2.urlopen(url)
            dataStr = data.read()
            if len(dataStr) < 10:
                return False
            else:
                return dataStr
        except:
            return False

    def getValue(self, key, values):
        value = values[key]
        if key in self.operationMap:
            val = float(value) / self.operationMap[key]
            ret = str(val)
        else:
            ret = value
        return ret.encode('utf-8')

    def outPutData(self, file_obj, datakey, data):
        tmp1 = re.sub(r'(\w+):','"\g<1>":', data)
        tmp = json.loads(self.coding(tmp1, 'gbk', 'utf-8'))
        for item1 in tmp:
            data_str = ''
            for key in datakey:
                val = str(self.getValue(key, item1)) + ","
                data_str += self.coding(val)
            if len(data_str) > 0:
                data_str = data_str[:-1]
            output = data_str + "\n"
            file_obj.write(output)

    def getData(self, urlMap):
        time_str = date.today()
        filename = self.coding(urlMap['name'] + '-' + str(time_str) + '.csv')
        print u"开始计算数据，输出到 ".encode('GBK'),
        print filename
        file_obj = open(filename, 'w')
        self.putFileTitle(file_obj, urlMap['fileTitle'])
        url = urlMap['url']
        param = urlMap['param']
        for i in range(1, self.page_num):
            param['page'] = i
            data = self.invoke(url, param)
            self.page_num = i
            if data == False:
                break
            self.outPutData(file_obj, urlMap['dataKey'], data)
        file_obj.close();
        output = "计算完成"
        print self.coding(output)


pro = StockData()
pro.demo()
