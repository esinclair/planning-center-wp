<?php

/**
 * Load the base class
 */
class Planning_Center_WP_Shortcodes
{

    function __construct()
    {

        add_shortcode('pcwp_events', array($this, 'events'));

    }


    public function events($atts)
    {
        $args = shortcode_atts(array(
            'group' => '',
            'past' => '',
            'future' => '',
            'filters',
            'parameters' => '',
        ), $atts);

        static $api;
        if ($api == null) {
            $api = new PCO_PHP_API;
        }
        list($events, $groups, $secondsRemaining, $groupTypes) = $this->getDataAndCache($api, $args);


        ob_start(); ?>

        <?php


        if (is_array($events)) {
            echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.css" rel="stylesheet"/>
                <link  href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.css" rel="stylesheet"/>
                <link  href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/list/main.css" rel="stylesheet"/>
                <link  href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.css" rel="stylesheet"/>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/main.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/daygrid/main.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/list/main.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/4.2.0/core/locales/es-us.js"></script>
                <div id="calendar" width="100%"></div>
                <script>
                        var calendarEl = document.getElementById(\'calendar\');
                
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            views: {
                                dayGrid: {},
                                weekGrid: {},
                                mothGrid: {}
                            },
                            header: {
                                left: \'prev,today,next\',
                                center: \'title\',
                                right: \'dayGridMonth,listMonth\'
                              },
                               contentHeight:"auto",
                          plugins: [
                              \'dayGrid\' ,
                              \'list\' 
                              ],
                              defaultView: \'listMonth\',
                timeZone: \'local\',
                events : [
                ';
            $options = get_option('planning_center_wp');
            foreach ($events as $event) {
                $groupInfo = $groups["".$event->relationships->group->data->id];
                echo '{';
                echo 'title: "' . $event->attributes->name;
                if(!($event->attributes->name == $groupInfo->attributes->name)){
                    echo ': ' . $groupInfo->attributes->name;
                }

                echo '",';
                echo 'start: "' . $event->attributes->starts_at . '", ';
                echo 'end: "' . $event->attributes->ends_at . '", ';
//                echo 'sldfkj: '.$groupInfo->relationships->group_type->data->id.', ';
                echo 'url: "https://'.$options['church_center_url'].'/groups/'. $this->pcUrlEncodeString($groupTypes[$groupInfo->relationships->group_type->data->id]->attributes->name) .'/'. $this->pcUrlEncodeString($groupInfo->attributes->name) .'" ';
                echo '},';
            }
            echo ']});calendar.render();</script>';
            echo $secondsRemaining." seconds until next refresh";
        } else {
            echo '<p class="planning-center-wp-not-found">No results found.</p>';
        }



        ?>

        <?php $content = ob_get_contents();
        ob_end_clean();
        return apply_filters('planning_center_wp_people_shortcode_output', $content);

    }



    /**
     * @param PCO_PHP_API $api
     * @param $args
     * @return array
     */
    public function getDataAndCache(PCO_PHP_API $api, $args)
    {
        $events = unserialize(get_post_meta(get_the_ID(), 'eventData', true));
        $groups = unserialize(get_post_meta(get_the_ID(), 'groupData', true));
        $refreshDate = intval(get_post_meta(get_the_ID(), 'refreshDate', true));
        $groupTypes = unserialize(get_post_meta(get_the_ID(), 'groupTypes', true));
        $baseRefreshTime = 60 * 60 * 24; // Refresh Daily
        $secondsRemaining = $baseRefreshTime - (time() - $refreshDate);
        if (($events == null) OR ($secondsRemaining < 0) OR ("true" == $_GET['refresh'])) {
            $events = $api->get_events($args);
            update_post_meta(get_the_ID(), 'eventData', serialize($events));
            $rawGroupTypes = $api->getGroupTypes();
            $groupTypes = array();
            echo "eliot";
            foreach ($rawGroupTypes as $grpType) {
                echo $grpType->attributes->name;
                $id2 = $grpType->id;
                $groupTypes[$id2] = $grpType;
            }
            update_post_meta(get_the_ID(), 'groupTypes', serialize($groupTypes));

            $rawgroups = $api->getAllGroups();
            $groups = array();
            foreach ($rawgroups as $grp) {
                $id2 = $grp->id;
                $groups[$id2] = $grp;
            }
            update_post_meta(get_the_ID(), 'groupData', serialize($groups));
            update_post_meta(get_the_ID(), 'refreshDate', time());
            $secondsRemaining = $baseRefreshTime;
        }
        return array($events, $groups, $secondsRemaining, $groupTypes);
    }

    /**
     * @param string
     * @return string
     */
    public function pcUrlEncodeString($str)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9\-]/', '-', str_replace(' ', '-', $str)));
    }


}


			