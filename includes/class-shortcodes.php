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
        list($events, $groups, $secondsRemaining) = $this->getDataAndCache($api, $args);


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
            foreach ($events as $event) {
                $groupInfo = $groups["".$event->relationships->group->data->id];
                echo '{';
                echo 'title: "' . $event->attributes->name . ': ' . $groupInfo->attributes->name . '",';
                echo 'start: "' . $event->attributes->starts_at . '", ';
                echo 'end: "' . $event->attributes->ends_at . '" ';
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
        $secondsRemaining = (60 * 5) - (time() - $refreshDate);
        if (($events == null) OR ($secondsRemaining < 0)) {
            $events = $api->get_events($args);
            update_post_meta(get_the_ID(), 'eventData', serialize($events));

            $rawgroups = $api->getAllGroups();
            $groups = array();
            foreach ($rawgroups as $grp) {
                $id2 = $grp->id;
                $groups[$id2] = $grp;
            }
            update_post_meta(get_the_ID(), 'groupData', serialize($groups));
            update_post_meta(get_the_ID(), 'refreshDate', time());
        }
        return array($events, $groups, $secondsRemaining);
    }


}


			