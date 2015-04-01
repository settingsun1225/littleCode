<?php
date_default_timezone_set('PRC');
class StockData
{
    const PAGENUM_FILE = 'stockdata_page.txt';
    private $file;
    private static $domain = 'http://vip.stock.finance.sina.com.cn/';
    private static $urlMap = array(
        1 => array(
            'name' => '主力净流入排名',
            'url' => 'quotes_service/api/json_v2.php/MoneyFlow.ssl_bkzj_ssggzj',
            'param' => array(
                'sort' => 'r0_net',
                'num' => 20,
                'asc' => 0,
                'bankuai' => '',
                'shichang' => '',
            ),
            'fileTitle' => '代码,名称,最新价,涨跌幅,换手率,成交额/万,主力流出/万,主力流入/万,主力净流入/万,主力静流入率,主力罗盘',
            'dataKey' => array('symbol', 'name', 'trade', 'changeratio', 'turnover', 'amount', 'r0_out', 'r0_in', 'r0_net', 'r0_ratio', 'r0x_ratio'),
        ),
        2 => array(
            'name' => '净流入排名',
            'url' => 'quotes_service/api/json_v2.php/MoneyFlow.ssl_bkzj_ssggzj',
            'param' => array(
                'sort' => 'netamount',
                'num' => 20,
                'asc' => 0,
                'bankuai' => '',
                'shichang' => '',
            ),
            'fileTitle' => '代码,名称,最新价,涨跌幅,换手率,成交额/万,流出资金/万,流入资金/万,净流入/万,静流入率,主力罗盘',
            'dataKey' => array('symbol', 'name', 'trade', 'changeratio', 'turnover', 'amount', 'outamount', 'inamount', 'netamount','ratioamount','r0x_ratio'),
        ),
    );

    private static $operationMap = array(
        'turnover' => array('division', 100),
        'r0_net' => array('division', 10000),
        'amount' => array('division', 10000),
        'r0_ratio' => array('division', 10000),
        'r0_out' => array('division', 10000),
        'r0_in' => array('division', 10000),
        'outamount' => array('division', 10000),
        'inamount' => array('division', 10000),
        'netamount' => array('division', 10000),

    );

    private $pageNum;
    
    public function start()
    {
        $updatePageNum = true;
        while(true) {
            $this->run($updatePageNum);
            $updatePageNum = false;
        }
    }

    private function run($updatePageNum = false)
    {
        if ($updatePageNum) {
            $this->pageNum = 0;
            if (file_exists(self::PAGENUM_FILE)) {
                $this->pageNum = intval(file_get_contents(self::PAGENUM_FILE));
            }
            echo "\n当前最大页码为 {$this->pageNum}，回车使用当前最大页码数，如需变更，请输入数字：";
            $input = strtolower(trim(fgets(STDIN)));
            if (!empty(intval($input))) {
                $this->pageNum = intval($input);
                file_put_contents(self::PAGENUM_FILE, $this->pageNum);
            }
        }

        echo "\n如下为股票数据编号及其内容\n";
        foreach (self::$urlMap as $index => $urlInfo) {
            echo "{$index} : {$urlInfo['name']}\n";
        }
        echo "\n请输入编号：";
        $way = strtolower(trim(fgets(STDIN)));
        $this->exec($way);
    }

    private function exec($way)
    {
        if (empty(self::$urlMap[$way])) {
            echo "输入编号错误\n";
            return;
        }
        $filename = self::$urlMap[$way]['name'] . '-' . date('Y-m-d') . '.csv';
        echo "计算数据，数据输出到 `{$filename}` 文件中\n";
        $this->file = fopen($filename, 'w');
        $this->putFileTitle(self::$urlMap[$way]['fileTitle']); 
        $url = self::$urlMap[$way]['url'];
        $param = self::$urlMap[$way]['param'];
        for ($page = 1; $page <= $this->pageNum; $page++) {
            $param['page'] = $page;
            $body = array();
            $ret = self::invoke($url, $param, $body);
            if (!$ret) {
                $data = self::convertData($body);
                $this->outPutData($data, self::$urlMap[$way]);
            } else {
                echo "运行到第{$page}页，程序出现bug\n";
            }
        }
        fclose($this->file);
        echo "处理完成\n";
    }

    private function putFileTitle($title)
    {
        $str = '';
        $titles = explode(',', $title);
        foreach ($titles as $title) {
            $val = iconv("UTF-8", "GBK", $title);
            $str .= "$val,";
        }
        $str = rtrim($str, ",");
        fwrite($this->file, $str."\n");
    }

    private function outPutData($datas, $map)
    {
        foreach ($datas as $data) {
            $str = '';
            foreach ($map['dataKey'] as $key) {
                $val = $data[$key];
                if (isset(self::$operationMap[$key][0])) {
                    $val = call_user_func(array('StockData', self::$operationMap[$key][0]), $val, self::$operationMap[$key][1]);
                }
                $val = iconv("UTF-8", "GBK", $val);
                $str .= "$val,";
            }
            $str = rtrim($str, ",");
            fwrite($this->file, $str."\n");
        }
    }

    private static function division($a, $d)
    {
        return $a / $d;
    }

    private static function convertData($data)
    {
        $encoding = mb_detect_encoding($data, array('ASCII', 'UTF-8', 'GBK', 'BIG5'));
        $s = iconv($encoding, 'UTF-8', $data);

        $s = str_replace("'", '"', $s);
        $s = preg_replace('/(\w+):/i', '"\1":', $s);
        return json_decode($s, true);
    }

    // 只有get调用
    private static function invoke($url, $param = array(), &$body = array())
    {
        $url = self::$domain . $url;
        if (!empty($param)) {
            $query_str = http_build_query($param);
            $url .= "?{$query_str}";
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, null);
        curl_setopt($curl, CURLOPT_POST, false);
        $body = curl_exec($curl);
        $errorCode = curl_errno($curl);
        if ($errorCode == CURLE_OK) {
            return false;
        } else {
            return 'errno=' . curl_errno($curl) . ' error=' . curl_error($curl);
        }
    }
}
$proc = new StockData();
$proc->start();
