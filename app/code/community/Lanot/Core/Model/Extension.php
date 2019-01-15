<?php

class Lanot_Core_Model_Extension
{
    const URL_FEED = 'http://www.lanot.biz/rss/catalog/extensions';
    const CACHE_TAG = 'LANOT_EXTENSIONS';
    const CACHE_LIFETIME = 7200;

    /**
     * @param $email
     * @return array
     */
    public function getFeedData($email) {
        $data = array();
        $url = self::URL_FEED;
        if (!empty($email)) {
            $url .= "?email=" . urlencode(base64_encode($email));
        }

        $cacheKey = 'lanot_extensions_' . md5($url);
        if (Mage::app()->useCache('config') && $cache = Mage::app()->loadCache($cacheKey)) {
            return unserialize($cache);
        }

        @$content = file_get_contents($url);
        if (!empty($content)) {
            $xml = new Varien_Simplexml_Config($content);
            if ($xml->getNode('channel/item')) {
                /** @var $item Varien_Simplexml_Element */
                foreach($xml->getNode('channel/item') as $item) {
                    $item = $item->asArray();
                    if (!empty($item['description'])) {
                       @$descr = json_decode($item['description'], true);
                        if (!empty($descr) && is_array($descr)) {
                            $item = array_merge($item, $descr);
                        } else {
                            continue;
                        }

                        if (isset($item['code'])) {
                            $data[$item['code']] = $item;
                        }
                    }
                }
            }
        }

        if (Mage::app()->useCache('config')) {
            Mage::app()->saveCache(serialize($data), $cacheKey, array(self::CACHE_TAG), self::CACHE_LIFETIME);
        }

        return $data;
    }
}
