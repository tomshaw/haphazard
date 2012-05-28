<?php
/**
 * 
 * @author Tom Shaw
 *
 */
class Plugin_Content_Assist extends Zend_Controller_Plugin_Abstract
{
    private $hasRun = null;
    
    protected $_request;
    
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        
        if (true === $this->hasRun) {
            return $request;
        }
        
        $module = $request->getModuleName();
        
        if ($module === 'admin') {
            return $request;
        }
        
        $controller = $request->getControllerName();
        
        $action = $request->getActionName();
        
        $view = Zend_Layout::getMvcInstance()->getView();
        
        $data = array();
        
        $view->placeholder('content_right')->setPrefix('<div id="content_right">')->setPostfix('</div>');
        
        switch ($controller) {
            case 'index':
                //$view->placeholder('content_right')->append($view->partial('partials/calender.phtml', $this->genCalendar()));
                $view->placeholder('content_right')->append($view->partial('partials/tags.phtml', $this->tagCloud($request)));
                //$view->placeholder('content_right')->append($view->partial('partials/poll.phtml', array('poll_data' => $this->genPollData())));
                //$view->placeholder('content_right')->append($view->partial('partials/twitter/friends.phtml', $this->twitterFriends()));
                break;
            case 'polls':
                //$view->placeholder('content_right')->append($view->partial('partials/poll.phtml', array('poll_data' => $this->genPollData())));
                //$view->placeholder('content_right')->append($view->partial('partials/calender.phtml', $this->genCalendar()));
                //$view->placeholder('content_right')->append($view->partial('partials/archives.phtml', $this->genArchives()));
                //$view->placeholder('content_right')->append($view->partial('partials/categories.phtml', $this->genCategories()));
                //$view->placeholder('content_right')->append($view->partial('partials/feeds.phtml', array()));
                break;
            case 'blog':
                if ($action !== 'feeds') {
                    //$view->placeholder('content_right')->append($view->partial('partials/calender.phtml', $this->genCalendar()));
                    //$view->placeholder('content_right')->append($view->partial('partials/archives.phtml', $this->genArchives()));
                    //$view->placeholder('content_right')->append($view->partial('partials/categories.phtml', $this->genCategories()));
                    //$view->placeholder('content_right')->append($view->partial('partials/feeds.phtml', array()));
                }
                break;
            case 'login';
                //$view->placeholder('content_right')->append($view->partial('partials/adds.phtml'));
                break;
            default:
                //$view->placeholder('content_right')->append($view->partial('partials/tags.phtml', $this->tagCloud($request)));
                //$view->placeholder('content_right')->append($view->partial('partials/twitter/friends.phtml', $this->twitterFriends()));
        }
        
        //$view->placeholder('content_footer')->append($view->partial('partials/footer.phtml', $this->twitterFriends()));
        
        $this->hasRun = true;
        
        return $request;
    }
    
    private function twitterFriends()
    {
        $config = $this->getTwitterConfigOptions();
        
        $token = new Zend_Oauth_Token_Access();
        $token->setToken($config['accessToken'])->setTokenSecret($config['accessTokenSecret']);
        
        $twitter = new Zend_Service_Twitter();
        $twitter->setLocalHttpClient($token->getHttpClient($config));
        
        try {
            //$response = $twitter->status->update('Testing OAuth.');
            $response = $twitter->user->friends();
        }
        catch (Exception $e) {
            return array();
        }
        
        $out = array();
        $col = $row = $count = 0;
        if (sizeof($response)) {
            foreach ($response as $data) {
                if ($count >= 50) {
                    break;
                }
                $out['friends'][$row][$col] = array(
                    'url' => 'http://twitter.com/' . $data->screen_name,
                    'name' => $data->name,
                    'image' => $data->profile_image_url,
                    'description' => $data->description
                );
                $col++;
                if ($col == 9) {
                    $row++;
                    $col = 0;
                }
                $count++;
            }
        }
        
        return $out;
    }
    
    public function toArray($data)
    {
        $array = array();
        foreach ($data as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }
    
    private function genTutorials()
    {
        $model = new Default_Model_Tutorials();
        
        $rows = $model->fetchTutorials($order = 'tutorial.id', $direction = 'DESC', $limit = 5);
        
        $tutorials = array();
        foreach ($rows as $row) {
            $tutorials['tutorials'][] = $row;
        }
        return $tutorials;
    }
    
    private function genCategories()
    {
        $model = new Default_Model_Entries();
        
        $all_data = $model->fetchAll();
        
        $cats = array();
        foreach ($all_data as $data) {
            if ($data->parent_id) {
                continue;
            }
            $cats['categories'][] = $data->toArray();
        }
        return $cats;
    }
    
    private function genPollData()
    {
        $model = new Default_Model_Polls();
        return $model->randomPoll();
    }
    
    private function genCalendar()
    {
        $year = $this->_request->getParam('year');
        
        $month = $this->_request->getParam('month');
        
        $day = $this->_request->getParam('day', 01);
        
        if ($year && $month && $day) {
            $date = mktime(0, 0, 0, $month, $day, $year);
        } else {
            $date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        }
        
        $day   = date('d', $date);
        $month = date('m', $date);
        $year  = date('Y', $date);
        
        $monthStart = mktime(0, 0, 0, $month, 1, $year);
        
        $monthName = date('F', $monthStart);
        
        $month_start_day = date('D', $monthStart);
        
        switch ($month_start_day) {
            case "Sun":
                $offset = 0;
                break;
            case "Mon":
                $offset = 1;
                break;
            case "Tue":
                $offset = 2;
                break;
            case "Wed":
                $offset = 3;
                break;
            case "Thu":
                $offset = 4;
                break;
            case "Fri":
                $offset = 5;
                break;
            case "Sat":
                $offset = 6;
                break;
        }
         
        if ($month == 1) {
            $numDaysLast = cal_days_in_month(0, 12, ($year - 1));
        } else {
            $numDaysLast = cal_days_in_month(0, ($month - 1), $year);
        }
        
        $numDaysCurrent = cal_days_in_month(0, $month, $year);
        
        for ($i = 1; $i <= $numDaysCurrent; $i++) {
            $numDaysArray[] = $i;
        }
        
        for ($i = 1; $i <= $numDaysLast; $i++) {
            $numDaysLastArray[] = $i;
        }
         
        if ($offset > 0) {
            $offsetCorrection = array_slice($numDaysLastArray, -$offset, $offset);
            $newCount         = array_merge($offsetCorrection, $numDaysArray);
            $offsetCount      = count($offsetCorrection);
        } else {
            $offsetCount = 0;
            $newCount    = $numDaysArray;
        }
        
        $currentNum = count($newCount);
         
        if ($currentNum > 35) {
            $numWeeks = 6;
            $outset    = (42 - $currentNum);
        } elseif ($currentNum < 35) {
            $numWeeks = 5;
            $outset    = (35 - $currentNum);
        }
        if ($currentNum == 35) {
            $numWeeks = 5;
            $outset    = 0;
        }
        
        for ($i = 1; $i <= $outset; $i++) {
            $newCount[] = $i;
        }
        
        $weeks = array_chunk($newCount, 7);
        
        $previousData = ($month == 1) ? mktime(0, 0, 0, 12, $day, ($year - 1)) : mktime(0, 0, 0, ($month - 1), $day, $year);
        $previousLink = '<a href="/archives/' . $this->formatArchiveDate($previousData) . '" title="View Previous Month" /><img src="/img/leftarrow.gif" border="0" width="13" height="17" /></a>';
        
        $nextData = ($month == 12) ? mktime(0, 0, 0, 1, $day, ($year + 1)) : mktime(0, 0, 0, ($month + 1), $day, $year);
        $nextLink = '<a href="/archives/' . $this->formatArchiveDate($nextData) . '" title="View Next Month" /><img src="/img/rightarrow.gif" border="0" width="13" height="17" /></a>';
        
        $caldata = array(
            'previous_link' => $previousLink,
            'month_name' => $monthName,
            'year_name' => $year,
            'next_link' => $nextLink
        );
        
        $timestamp = mktime(gmdate('H', time()), gmdate('i', time()), gmdate('s', time()), gmdate('n', time()), gmdate('d', time()), gmdate('Y', time()));
        
        $model = new Default_Model_Entries();
        
        $entries = $model->queryEntries(null);
        
        $data = array();
        foreach ($entries as $entry) {
            $data[date('d', $entry->created) . '/' . date('m', $entry->created) . '/' . date('Y', $entry->created)][] = $entry->created;
        }
        
        $i   = 0;
        $x   = 0;
        $out = array();
        foreach ($weeks as $week) {
            foreach ($week as $day) {
                $row = false;
                if ($i < $offsetCount) {
                    $link  = $day;
                    $class = 'prev';
                }
                if (($i >= $offsetCount) && ($i < ($numWeeks * 7) - $outset)) {
                    $datecount = mktime(0, 0, 0, $month - 1, $day, $year);
                    $link      = $day;
                    if (isset($data[$day . '/' . $month . '/' . $year])) {
                        $link  = '<a href="/archives/' . $year . '/' . $month . '/' . $day . '" title="View Entry" />' . $day . '</a>';
                        $class = 'link';
                    } else {
                        if (mktime(0, 0, 0, date('m'), date('d'), date('Y')) == mktime(0, 0, 0, $month, $day, $year)) {
                            $link  = $day;
                            $class = 'today';
                        } else {
                            $link;
                            $class = 'current';
                        }
                    }
                } elseif ($outset > 0) {
                    if (($i >= ($numWeeks * 7) - $outset)) {
                        $link  = $day;
                        $class = 'next';
                    }
                }
                $i++;
                $out[$x][] = array(
                    'link' => $link,
                    'class' => $class
                );
            }
            $x++;
        }
        
        $caldata['weeks'] = $out;
        
        return $caldata;
    }
    
    private function formatArchiveDate($timestamp, $start_day = false)
    {
        $day   = date('d', $timestamp);
        $month = date('m', $timestamp);
        $year  = date('Y', $timestamp);
        return ($start_day) ? $year . '/' . $month . '/' . $day : $year . '/' . $month;
    }
    
    private function genArchives($max_x = 11, $show_count = true, $freq = 'months')
    {
        $ts = mktime(0, 0, 0, date('m'), 1);
        for ($x = 0; $x < $max_x; $x++) {
            $current_ts = $ts;
            switch ($freq) {
                case 'months':
                    $linkStamp = date('Y/m', $ts);
                    $ts_title  = date('F Y', $ts);
                    $ts        = mktime(0, 0, 0, date('m', $ts) - 1, 1, date('Y', $ts)); // Must be last in 'case' statement
                    break;
                case 'weeks':
                    $linkStamp = date('Y/\WW', $ts);
                    $ts_title  = WEEK . ' ' . date('W, Y', $ts);
                    $ts        = mktime(0, 0, 0, date('m', $ts), date('d', $ts) - 7, date('Y', $ts));
                    break;
                case 'days':
                    $linkStamp = date('Y/m/d', $ts);
                    $ts_title  = date("%B %e. %Y", $ts);
                    $ts        = mktime(0, 0, 0, date('m', $ts), date('d', $ts) - 1, date('Y', $ts)); // Must be last in 'case' statement
                    break;
            }
            
            $html_count = '';
            if ($show_count) {
                switch ($freq) {
                    case 'months':
                        $end_ts = $current_ts + (date('t', $current_ts) * 24 * 60 * 60) - 1;
                        break;
                    case 'weeks':
                        $end_ts = $current_ts + (7 * 24 * 60 * 60) - 1;
                        break;
                    case 'days':
                        $end_ts = $current_ts + (24 * 60 * 60) - 1;
                        break;
                }
                
                // TODO Redo outside of the loop.
                $model = new Default_Model_Entries();
                
                $entries = $model->fetchMonthlyCount($current_ts, $end_ts);
                
                if (is_array($entries)) {
                    if (empty($entries[0]->orderkey)) {
                        $entries[0]->orderkey = '0';
                    }
                    $html_count .= $entries[0]->orderkey;
                }
            }
            
            $data          = array();
            $data['link']  = $linkStamp;
            $data['title'] = $ts_title;
            $data['count'] = $html_count;
            $output[$x]    = $data;
            
        }
        $out['archives'] = $output;
        return $out;
    }
    
    private function tagCloud($request)
    {
        $tagCloud['cloud'] = new Zend_Tag_Cloud(array(
            'tags' => array(
                array(
                    'title' => 'AJAX',
                    'weight' => 7,
                    'params' => array(
                        'url' => '/tag/ajax'
                    )
                ),
                array(
                    'title' => 'Zend Framework',
                    'weight' => 50,
                    'params' => array(
                        'url' => '/tag/zend-framework'
                    )
                ),
                array(
                    'title' => 'PHP',
                    'weight' => 44,
                    'params' => array(
                        'url' => '/tag/php'
                    )
                ),
                array(
                    'title' => 'Zend_Application',
                    'weight' => 5,
                    'params' => array(
                        'url' => '/tag/zend-application'
                    )
                ),
                array(
                    'title' => 'Search',
                    'weight' => 11,
                    'params' => array(
                        'url' => '/tag/search'
                    )
                ),
                array(
                    'title' => 'SVN',
                    'weight' => 21,
                    'params' => array(
                        'url' => '/tag/svn'
                    )
                ),
                array(
                    'title' => 'Zend',
                    'weight' => 44,
                    'params' => array(
                        'url' => '/tag/zend'
                    )
                ),
                array(
                    'title' => 'Google',
                    'weight' => 5,
                    'params' => array(
                        'url' => '/tag/google'
                    )
                ),
                array(
                    'title' => 'podcast',
                    'weight' => 44,
                    'params' => array(
                        'url' => '/tag/podcast'
                    )
                ),
                array(
                    'title' => 'Postgres',
                    'weight' => 22,
                    'params' => array(
                        'url' => '/tag/postgres'
                    )
                ),
                array(
                    'title' => 'X-Debug',
                    'weight' => 55,
                    'params' => array(
                        'url' => '/tag/x-debug'
                    )
                ),
                array(
                    'title' => 'Zend_Loader',
                    'weight' => 45,
                    'params' => array(
                        'url' => '/tag/zend-loader'
                    )
                ),
                array(
                    'title' => 'Linux',
                    'weight' => 5,
                    'params' => array(
                        'url' => '/tag/linux'
                    )
                ),
                array(
                    'title' => 'MySQL',
                    'weight' => 22,
                    'params' => array(
                        'url' => '/tag/mysql'
                    )
                ),
                array(
                    'title' => 'Oracle',
                    'weight' => 21,
                    'params' => array(
                        'url' => '/tag/oracle'
                    )
                ),
                array(
                    'title' => 'Unit Testing',
                    'weight' => 11,
                    'params' => array(
                        'url' => '/tag/unit-testing'
                    )
                ),
                array(
                    'title' => 'Eclipse',
                    'weight' => 51,
                    'params' => array(
                        'url' => '/tag/eclipse'
                    )
                ),
                array(
                    'title' => 'Zend Studio',
                    'weight' => 11,
                    'params' => array(
                        'url' => '/tag/zend-studio'
                    )
                ),
                array(
                    'title' => 'Zend_Application',
                    'weight' => 22,
                    'params' => array(
                        'url' => '/tag/zend-application'
                    )
                ),
                array(
                    'title' => 'IDE',
                    'weight' => 44,
                    'params' => array(
                        'url' => '/tag/ide'
                    )
                ),
                array(
                    'title' => 'session',
                    'weight' => 24,
                    'params' => array(
                        'url' => '/tag/session'
                    )
                ),
                array(
                    'title' => 'ACL',
                    'weight' => 46,
                    'params' => array(
                        'url' => '/tag/acl'
                    )
                )
            ),
            //		    'prefixPath' => array(
            //        		'prefix' => 'App_Decorator_',
            //        		'path'   => dirname(APPLICATION_PATH) . '/App/Decorator'
            //		    ),
            'cloudDecorator' => array(
                'decorator' => 'HtmlCloud',
                'options' => array(
                    'HtmlTags' => array(
                        'div' => array(
                            'class' => 'tag'
                        )
                    ),
                    'separator' => ' '
                )
            ),
            'tagDecorator' => array(
                'decorator' => 'HtmlTag',
                'options' => array(
                    'HtmlTags' => array(
                        'span'
                    )
                )
            )
        ));
        return $tagCloud;
    }
    
    private function getTwitterConfigOptions()
    {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/system/keys.ini', 'production');
        return $config->twitter->toArray();
    }
    
    private function getTwitterOptions()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        $config    = $bootstrap->getOptions();
        return $config['twitter'];
    }
    
}