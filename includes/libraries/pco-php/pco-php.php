<?php

use MongoDB\BSON\UTCDateTime;

/**
 * Load the base class for the PCO PHP API
 * // http://planningcenter.github.io/api-docs/#schedules
 */
class PCO_PHP_API
{

    protected $app_id;
    protected $secret;
    private static $groups = array();


    function __construct()
    {

        $options = get_option('planning_center_wp');

        $this->app_id = $options['app_id'];
        $this->secret = $options['secret'];

    }


    public function get_events($args = '')
    {

        $events = new PCO_PHP_Events($args);
        if ($args['group'] != null) {
            $url = 'https://api.planningcenteronline.com/groups/v2/groups/' . $args['group'] . '/events?order=starts_at';
        } else {
            $url = 'https://api.planningcenteronline.com/groups/v2/events?order=starts_at';
        }
        $result = $this->getAllPagesOfData($url);
        $past = $args['past'];
        $future = $args['future'];
        $now = new DateTime();
        $start = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $result[0]->attributes->starts_at);
        if ($past != '') {
            $nextIndex = 0;
            do {
                $start = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $result[$nextIndex]->attributes->starts_at);
                $nextIndex = $nextIndex + 1;
            } while ($now > $start);
//		    echo 'next index: '.$nextIndex;
            if ($past < $nextIndex) {
                $past = $nextIndex - $past;
                while ($past > 1) {
                    array_shift($result);
                    $past = $past - 1;
                }
            }
        }
        if ($future != '') {
            $result = array_reverse($result);
            $nextIndex = 0;
            do {
                $start = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $result[$nextIndex]->attributes->starts_at);
                $nextIndex = $nextIndex + 1;
            } while ($now < $start);

            while ($nextIndex - 1 > $future) {
                array_shift($result);
                $nextIndex -= 1;
            }
            $result = array_reverse($result);
        }

        return $result;

    }

    public function get_headers()
    {
        return array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->app_id . ':' . $this->secret)
            )
        );
    }

    public function get_group_details($id)
    {
        $id1 = intval($id);
        if (self::$groups[$id1] == null) {
            $allgroups = $this->getAllPagesOfData('https://api.planningcenteronline.com/groups/v2/groups?');
            foreach ($allgroups as $grp) {
                $id2 = intval($grp->id);
               self::$groups[$id2] = $grp;
            }
        }
        $result = self::$groups[$id1];
        if($result == null){
            error_log("null result " . $id1);
        }
        return $result;
    }

    public function getAllPagesOfData($url)
    {
        return $this->getAllPagesOfDataWithOffset($url, 0);
    }


    /**
     * @param $url
     * @return string
     */
    public function getAllPagesOfDataWithOffset($url, $offset)
    {
        $urlOffset = $url . '&per_page=100&&offset=' . $offset;
        $response = wp_remote_get($urlOffset, $this->get_headers());
        $result = '';

        if (is_array($response)) {
            $header = $response['headers']; // array of http header lines
            $body = json_decode($response['body']); // use the content
            $result = $body->data;
            if ($body->meta->next != null && $body->meta->next->offset > 0) {
                $result = array_merge($result, $this->getAllPagesOfDataWithOffset($url, ($offset + 100)));
            }
        }
        return $result;
    }


}